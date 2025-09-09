<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Vehicle;
use App\Models\Maintenance;
use App\Services\MaintenanceService;

class MaintenanceController extends Controller
{
    private MaintenanceService $maintenanceService;
    private string $maintenanceApi = '/api/maintenances';

    public function __construct(MaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * Helper to fetch one maintenance record either via API or internal service.
     */
    private function getMaintenanceJson(int $id, bool $useApi): ?array
    {
        if ($useApi) {
            $response = Http::timeout(10)->get(url($this->maintenanceApi . '/' . $id));
            if ($response->failed()) {
                return null;
            }
            // Maintenance API returns raw model JSON
            return $response->json();
        } else {
            $jsonResponse = $this->maintenanceService->find($id);
            if ($jsonResponse instanceof \Illuminate\Http\JsonResponse) {
                return $jsonResponse->getData(true)['data'] ?? null;
            }
            return is_array($jsonResponse) ? $jsonResponse : null;
        }
    }

    // ---------- LIST ----------
    // GET /maintenance
    public function index(Request $request)
    {
        $useApi    = (bool) $request->query('use_api', false);
        $vehicleId = $request->query('vehicle_id');
        $perPage   = 10;

        if ($useApi) {
            // Call API (optionally filter by vehicle)
            if ($vehicleId) {
                $resp = Http::get(url("/api/vehicles/{$vehicleId}/maintenances"));
            } else {
                $resp = Http::get(url($this->maintenanceApi));
            }

            $array = $resp->ok() ? (array) $resp->json() : [];
            // If byVehicle: API returns ['0'=>{...}, ...] or ['data'=>[...]] depending on your version.
            // Normalize to a flat array of items.
            $items = collect(isset($array['data']) ? $array['data'] : $array);

            // Build a paginator so the Blade keeps working with ->links()
            $page    = LengthAwarePaginator::resolveCurrentPage();
            $slice   = $items->slice(($page - 1) * $perPage, $perPage)->values();
            $records = new LengthAwarePaginator(
                $slice,
                $items->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('maintenance.index', ['records' => $records]);
        }

        // Internal (DB) path with eager load + pagination
        $records = Maintenance::with('vehicle')
            ->when($vehicleId, fn($q) => $q->where('vehicle_id', $vehicleId))
            ->latest()
            ->paginate($perPage);

        return view('maintenance.index', ['records' => $records]);
    }

    // ---------- CREATE FORM ----------
    // GET /maintenance/create
    public function create()
    {
        $vehicles = Vehicle::where('availability_status', Vehicle::AVAILABLE)
            ->orderBy('id')
            ->get();

        return view('maintenance.create', compact('vehicles'));
    }

    // ---------- STORE ----------
    // POST /maintenance
    public function store(Request $request)
    {
        $useApi = (bool) $request->query('use_api', false);

        $validated = $request->validate([
            'vehicle_id'       => 'required|integer|exists:vehicles,id',
            'maintenance_type' => 'required|string|max:50',
            'service_date'     => 'required|date|after_or_equal:today',
            'cost'             => 'required|numeric|min:1',
            'notes'            => 'nullable|string|max:500',
        ]);

        // Same business rules for both paths (so data integrity is guaranteed)
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        // Guard 1: vehicle must be available
        if ($vehicle->availability_status !== Vehicle::AVAILABLE) {
            return back()
                ->withErrors(['vehicle_id' => 'This vehicle is not available to schedule maintenance.'])
                ->withInput();
        }

        // Guard 2: only one Scheduled per vehicle
        $alreadyScheduled = Maintenance::where('vehicle_id', $vehicle->id)
            ->where('status', 'Scheduled')
            ->exists();

        if ($alreadyScheduled) {
            return back()
                ->withErrors(['vehicle_id' => 'This vehicle already has a scheduled maintenance.'])
                ->withInput();
        }

        if ($useApi) {
            // External path: call your own API to create
            $payload = $validated + ['status' => 'Scheduled'];
            $resp = Http::post(url($this->maintenanceApi), $payload);

            if ($resp->failed()) {
                return back()->withErrors(['api' => 'Failed to create maintenance via API'])->withInput();
            }

            // Mirror the state change locally so UI remains consistent (optional)
            DB::transaction(function () use ($vehicle) {
                $vehicle->getState()->markAsUnderMaintenance();
            });

            return redirect()->route('maintenance.index')->with('ok', 'Maintenance successfully scheduled.');
        }

        // Internal path: DB + state pattern
        DB::transaction(function () use ($validated, $vehicle) {
            // State Pattern: Available -> Under Maintenance
            $vehicle->getState()->markAsUnderMaintenance();

            Maintenance::create([
                'vehicle_id'       => $vehicle->id,
                'maintenance_type' => $validated['maintenance_type'],
                'service_date'     => $validated['service_date'],
                'cost'             => $validated['cost'],
                'notes'            => $validated['notes'] ?? null,
                'status'           => 'Scheduled',
            ]);
        });

        return redirect()->route('maintenance.index')->with('ok', 'Maintenance successfully scheduled.');
    }

    // ---------- EDIT FORM ----------
    // GET /maintenance/{maintenance}/edit
    public function edit(Maintenance $maintenance)
    {   
        $maintenance->load('vehicle');
        $vehicles = Vehicle::orderBy('id')->get();
        return view('maintenance.edit', compact('maintenance', 'vehicles'));
    }

    // ---------- UPDATE ----------
    // PUT /maintenance/{maintenance}
    public function update(Request $request, Maintenance $maintenance)
    {
        $useApi = (bool) $request->query('use_api', false);

        $request->validate([
            'maintenance_type' => 'required|string|max:50',
            'service_date'     => 'required|date',
            'status'           => 'required|in:Scheduled,Completed,Cancelled',
            'cost'             => 'required|numeric|min:1',
            'notes'            => 'nullable|string',
        ]);

        if ($useApi) {
            // External path: call your own API to update
            $payload = [
                'maintenance_type' => $request->maintenance_type,
                'service_date'     => $request->service_date,
                'status'           => $request->status,
                'cost'             => $request->cost,
                'notes'            => $request->notes,
            ];

            $resp = Http::put(url($this->maintenanceApi . '/' . $maintenance->id), $payload);

            if ($resp->failed()) {
                return back()->withErrors(['api' => 'Failed to update maintenance via API'])->withInput();
            }

            // Optional local state sync (keeps vehicle availability consistent if needed)
            $vehicle    = $maintenance->vehicle;
            $fromStatus = $maintenance->status;
            $toStatus   = $request->status;

            DB::transaction(function () use ($vehicle, $fromStatus, $toStatus) {
                if ($fromStatus !== 'Completed' && $toStatus === 'Completed') {
                    // nothing to change locally in maintenance (API already did),
                    // but we may need to release vehicle
                    if ($vehicle->availability_status === Vehicle::UNDER_MAINTENANCE) {
                        $vehicle->getState()->markAsAvailable();
                    }
                } elseif ($toStatus === 'Scheduled') {
                    if ($vehicle->availability_status !== Vehicle::UNDER_MAINTENANCE) {
                        $vehicle->getState()->markAsUnderMaintenance();
                    }
                }
            });

            return redirect()->route('maintenance.index')->with('ok', 'Maintenance record successfully updated.');
        }

        // Internal path: DB + state transitions
        $vehicle    = $maintenance->vehicle;
        $fromStatus = $maintenance->status;
        $toStatus   = $request->status;

        DB::transaction(function () use ($request, $maintenance, $vehicle, $fromStatus, $toStatus) {
            $maintenance->fill([
                'maintenance_type' => $request->maintenance_type,
                'service_date'     => $request->service_date,
                'status'           => $toStatus,
                'cost'             => $request->cost,
                'notes'            => $request->notes,
            ]);

            // completed_at handling
            if ($fromStatus !== 'Completed' && $toStatus === 'Completed') {
                $maintenance->completed_at = now();
            } elseif ($fromStatus === 'Completed' && $toStatus !== 'Completed') {
                $maintenance->completed_at = null;
            }

            $maintenance->save();

            // Vehicle state transitions
            if (in_array($toStatus, ['Completed', 'Cancelled'])) {
                if ($vehicle->availability_status === Vehicle::UNDER_MAINTENANCE) {
                    $vehicle->getState()->markAsAvailable();
                }
            } elseif ($toStatus === 'Scheduled') {
                if ($vehicle->availability_status !== Vehicle::UNDER_MAINTENANCE) {
                    $vehicle->getState()->markAsUnderMaintenance();
                }
            }
        });

        return redirect()->route('maintenance.index')->with('ok', 'Maintenance record successfully updated.');
    }

    // ---------- DELETE ----------
    // DELETE /maintenance/{maintenance}
    public function destroy(Maintenance $maintenance)
    {
        $useApi = (bool) request()->query('use_api', false);

        if ($useApi) {
            $resp = Http::delete(url($this->maintenanceApi . '/' . $maintenance->id));
            if ($resp->failed()) {
                return back()->withErrors(['api' => 'Failed to delete maintenance via API']);
            }

            // Optional local state release if needed
            $vehicle      = $maintenance->vehicle;
            $wasScheduled = $maintenance->status === 'Scheduled';

            if ($wasScheduled) {
                $stillScheduled = Maintenance::where('vehicle_id', $vehicle->id)
                    ->where('status', 'Scheduled')
                    ->exists();

                if (!$stillScheduled && $vehicle->availability_status === Vehicle::UNDER_MAINTENANCE) {
                    $vehicle->getState()->markAsAvailable();
                }
            }

            return redirect()->route('maintenance.index')->with('ok', 'Maintenance record successfully deleted');
        }

        // Internal path
        $vehicle      = $maintenance->vehicle;
        $wasScheduled = $maintenance->status === 'Scheduled';

        $maintenance->delete();

        if ($wasScheduled) {
            $stillScheduled = Maintenance::where('vehicle_id', $vehicle->id)
                ->where('status', 'Scheduled')
                ->exists();

            if (!$stillScheduled && $vehicle->availability_status === Vehicle::UNDER_MAINTENANCE) {
                $vehicle->getState()->markAsAvailable();
            }
        }

        return redirect()->route('maintenance.index')->with('ok', 'Maintenance record successfully deleted');
    }
}