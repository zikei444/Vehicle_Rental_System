<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Maintenance;
use App\Services\MaintenanceService;

class MaintenanceController extends Controller
{
    private MaintenanceService $maintenanceService;
    private string $maintenanceApi = '/api/maintenances';
    private string $vehicleApi     = '/api/vehicles';

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
            // Maintenance API returns raw model JSON (not wrapped)
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
                $resp = Http::get(url("{$this->vehicleApi}/{$vehicleId}/maintenances"));
            } else {
                $resp = Http::get(url($this->maintenanceApi));
            }

            $array = $resp->ok() ? (array) $resp->json() : [];
            // normalize both shapes: ['data'=> [...]] or just [...]
            $items = collect(isset($array['data']) ? $array['data'] : $array);

            // Build a paginator so Blade can still use ->links()
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
        else {
            // Internal (DB) path with eager load + pagination
            $records = Maintenance::with('vehicle')
                ->when($vehicleId, fn($q) => $q->where('vehicle_id', $vehicleId))
                ->latest()
                ->paginate($perPage);

            return view('maintenance.index', ['records' => $records]);
        }
    }

    // ---------- CREATE FORM ----------
    // GET /maintenance/create
    public function create(Request $request)
    {
        $useApi = (bool) $request->query('use_api', false);

        if ($useApi) {
            // Pull vehicles from API and (optionally) filter to 'available'
            $resp = Http::get(url($this->vehicleApi));
            if ($resp->failed()) {
                // on failure, just send empty list
                $vehicles = collect();
            } else {
                $list = $resp->json()['data'] ?? $resp->json() ?? [];
                // if your API supports query filtering, you could call /api/vehicles?status=available instead
                $vehicles = collect($list)->filter(fn($v) => ($v['availability_status'] ?? '') === 'available')->values();
            }

            // Pass as array of arrays; Blade reads data-* attributes from these
            return view('maintenance.create', ['vehicles' => $vehicles]);
        }

        // Internal path: you used to query Vehicle model; since we avoid Vehicle::,
        // we also fetch via API here for consistency.
        $resp = Http::get(url($this->vehicleApi));
        if ($resp->failed()) {
            $vehicles = collect();
        } else {
            $list = $resp->json()['data'] ?? $resp->json() ?? [];
            $vehicles = collect($list)->filter(fn($v) => ($v['availability_status'] ?? '') === 'available')->values();
        }
        return view('maintenance.create', ['vehicles' => $vehicles]);
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

        // 1) Read vehicle via API
        $vehResp = Http::get(url($this->vehicleApi . '/' . $validated['vehicle_id']));
        if ($vehResp->failed()) {
            return back()->withErrors(['vehicle_id' => 'Vehicle not found'])->withInput();
        }
        $vehicle = $vehResp->json()['data'] ?? $vehResp->json() ?? null;
        if (!$vehicle) {
            return back()->withErrors(['vehicle_id' => 'Vehicle not found'])->withInput();
        }

        // 2) Guard: vehicle must be available
        if (($vehicle['availability_status'] ?? '') !== 'available') {
            return back()
                ->withErrors(['vehicle_id' => 'This vehicle is not available to schedule maintenance.'])
                ->withInput();
        }

        // 3) Guard: only one Scheduled maintenance per vehicle
        $msResp = Http::get(url($this->vehicleApi . '/' . $validated['vehicle_id'] . '/maintenances'));
        if ($msResp->failed()) {
            return back()->withErrors(['vehicle_id' => 'Cannot verify existing maintenances for this vehicle'])->withInput();
        }
        $list = $msResp->json()['data'] ?? $msResp->json() ?? [];
        $alreadyScheduled = collect($list)->contains(fn ($m) => ($m['status'] ?? '') === 'Scheduled');
        if ($alreadyScheduled) {
            return back()
                ->withErrors(['vehicle_id' => 'This vehicle already has a scheduled maintenance.'])
                ->withInput();
        }

        if ($useApi) {
            // 4A) External path: create maintenance via API
            $payload = $validated + ['status' => 'Scheduled'];
            $resp = Http::post(url($this->maintenanceApi), $payload);
            if ($resp->failed()) {
                return back()->withErrors(['api' => 'Failed to create maintenance via API'])->withInput();
            }

            // 5A) Flip vehicle status via API
            Http::post(url($this->vehicleApi . '/update-status'), [
                'vehicle_id' => $validated['vehicle_id'],
                'status'     => 'under_maintenance',
            ]);

            return redirect()->route('maintenance.index')->with('ok', 'Maintenance successfully scheduled.');
        } 
        else {
            // 4B) Internal path: create maintenance row locally
            DB::transaction(function () use ($validated) {
                Maintenance::create([
                    'vehicle_id'       => $validated['vehicle_id'],
                    'maintenance_type' => $validated['maintenance_type'],
                    'service_date'     => $validated['service_date'],
                    'cost'             => $validated['cost'],
                    'notes'            => $validated['notes'] ?? null,
                    'status'           => 'Scheduled',
                ]);
            });

            // 5B) Flip vehicle status via API (no local Vehicle::)
            Http::post(url($this->vehicleApi . '/update-status'), [
                'vehicle_id' => $validated['vehicle_id'],
                'status'     => 'under_maintenance',
            ]);

            return redirect()->route('maintenance.index')->with('ok', 'Maintenance successfully scheduled.');
        }
    }

    // ---------- EDIT FORM ----------
    // GET /maintenance/{maintenance}/edit
    public function edit(Maintenance $maintenance, Request $request)
    {
        // If your Blade reads $maintenance->vehicle (relation), you can keep eager-load here.
        // This does not use Vehicle:: constants; it just loads related model for display.
        $maintenance->load('vehicle');

        return view('maintenance.edit', compact('maintenance'));
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

        $toStatus = $request->status;

        if ($useApi) {
            // Update maintenance via API
            $payload = [
                'maintenance_type' => $request->maintenance_type,
                'service_date'     => $request->service_date,
                'status'           => $toStatus,
                'cost'             => $request->cost,
                'notes'            => $request->notes,
            ];

            $resp = Http::put(url($this->maintenanceApi . '/' . $maintenance->id), $payload);
            if ($resp->failed()) {
                return back()->withErrors(['api' => 'Failed to update maintenance via API'])->withInput();
            }

            // Adjust vehicle status via API (mirror your previous logic)
            if (in_array($toStatus, ['Completed', 'Cancelled'])) {
                Http::post(url($this->vehicleApi . '/update-status'), [
                    'vehicle_id' => $maintenance->vehicle_id,
                    'status'     => 'available',
                ]);
            } elseif ($toStatus === 'Scheduled') {
                Http::post(url($this->vehicleApi . '/update-status'), [
                    'vehicle_id' => $maintenance->vehicle_id,
                    'status'     => 'under_maintenance',
                ]);
            }

            return redirect()->route('maintenance.index')->with('ok', 'Maintenance record successfully updated.');
        } 
        else {
            // Internal: update row locally; adjust vehicle via API (no Vehicle::)
            DB::transaction(function () use ($request, $maintenance, $toStatus) {
                $fromStatus = $maintenance->status;

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
            });

            // Vehicle status via API (no local state objects)
            if (in_array($toStatus, ['Completed', 'Cancelled'])) {
                Http::post(url($this->vehicleApi . '/update-status'), [
                    'vehicle_id' => $maintenance->vehicle_id,
                    'status'     => 'available',
                ]);
            } elseif ($toStatus === 'Scheduled') {
                Http::post(url($this->vehicleApi . '/update-status'), [
                    'vehicle_id' => $maintenance->vehicle_id,
                    'status'     => 'under_maintenance',
                ]);
            }

            return redirect()->route('maintenance.index')->with('ok', 'Maintenance record successfully updated.');
        }
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

            // After deleting, decide vehicle status. Simplest: set 'available'.
            Http::post(url($this->vehicleApi . '/update-status'), [
                'vehicle_id' => $maintenance->vehicle_id,
                'status'     => 'available',
            ]);

            return redirect()->route('maintenance.index')->with('ok', 'Maintenance record successfully deleted');
        } 
        else {
            $vehicleId    = $maintenance->vehicle_id;
            $wasScheduled = $maintenance->status === 'Scheduled';

            $maintenance->delete();

            if ($wasScheduled) {
                // check remaining scheduled maintenances via API
                $msResp = Http::get(url($this->vehicleApi . '/' . $vehicleId . '/maintenances'));
                $list = $msResp->json()['data'] ?? $msResp->json() ?? [];
                $stillScheduled = collect($list)->contains(fn ($m) => ($m['status'] ?? '') === 'Scheduled');

                if (!$stillScheduled) {
                    Http::post(url($this->vehicleApi . '/update-status'), [
                        'vehicle_id' => $vehicleId,
                        'status'     => 'available',
                    ]);
                }
            }

            return redirect()->route('maintenance.index')->with('ok', 'Maintenance record successfully deleted');
        }
    }
}