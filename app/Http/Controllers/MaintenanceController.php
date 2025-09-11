<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Maintenance;
use App\Services\MaintenanceService;
use App\Services\VehicleService;

class MaintenanceController extends Controller
{
    private MaintenanceService $maintenanceService;
    private VehicleService $vehicleService;

    private string $maintenanceApi = '/api/maintenances';
    private string $vehicleApi     = '/api/vehicles';

    public function __construct(MaintenanceService $maintenanceService, VehicleService $vehicleService)
    {
        $this->maintenanceService = $maintenanceService;
        $this->vehicleService     = $vehicleService;
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
            // API returns raw model JSON (not wrapped)
            return $response->json();
        }

        $jsonResponse = $this->maintenanceService->find($id);
        if ($jsonResponse instanceof \Illuminate\Http\JsonResponse) {
            return $jsonResponse->getData(true)['data'] ?? null;
        }
        return is_array($jsonResponse) ? $jsonResponse : null;
    }

    // ---------- LIST ----------
    // GET /maintenance
    public function index(Request $request)
    {
        $useApi    = (bool) $request->query('use_api', false);
        $vehicleId = $request->query('vehicle_id');
        $perPage   = 10;

        $search    = $request->query('search');
        $filter    = $request->query('status');   // Scheduled, Completed, Cancelled
        $sortParam = $request->query('sort');     // may be null/empty
        $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['service_date', 'created_at', 'updated_at', 'cost'];
        $sort = in_array($sortParam, $allowedSorts, true) ? $sortParam : null;

        if ($useApi) {
            // --- API path ---
            $resp  = Http::get(url($this->maintenanceApi));
            $array = $resp->ok() ? (array) $resp->json() : [];
            $items = collect(isset($array['data']) ? $array['data'] : $array);

            if ($vehicleId) {
                $items = $items->where('vehicle_id', (int) $vehicleId);
            }

            if ($search) {
                $needle = mb_strtolower($search);
                $items = $items->filter(function ($r) use ($needle) {
                    $in = static fn($v) => str_contains(mb_strtolower((string) $v), $needle);
                    return $in($r['maintenance_type'] ?? '')
                        || $in($r['notes'] ?? '')
                        || $in($r['cost'] ?? '')
                        || $in($r['vehicle']['brand'] ?? '')
                        || $in($r['vehicle']['model'] ?? '')
                        || $in($r['vehicle']['registration_number'] ?? '');
                });
            }

            if ($filter) {
                $items = $items->where('status', $filter);
            }

            // Sort (fallback to id desc)
            if ($sort) {
                $items = $items->sortBy([[$sort, $direction === 'asc' ? SORT_ASC : SORT_DESC]]);
            } else {
                $items = $items->sortByDesc('id');
            }

            // Paginate the collection
            $page    = LengthAwarePaginator::resolveCurrentPage();
            $slice   = $items->slice(($page - 1) * $perPage, $perPage)->values();
            $records = new LengthAwarePaginator(
                $slice,
                $items->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('maintenance.index', compact('records', 'search', 'filter', 'sort', 'direction'));
        }

        // --- DB path ---
        $query = Maintenance::with('vehicle')
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('maintenance_type', 'like', "%{$search}%")
                       ->orWhere('notes', 'like', "%{$search}%")
                       ->orWhere('cost', 'like', "%{$search}%")
                       ->orWhereHas('vehicle', fn ($v) =>
                           $v->where('brand', 'like', "%{$search}%")
                             ->orWhere('model', 'like', "%{$search}%")
                             ->orWhere('registration_number', 'like', "%{$search}%"));
                });
            })
            ->when($filter, fn ($q) => $q->where('status', $filter))
            ->when($sort, fn ($q) => $q->orderBy($sort, $direction))
            ->orderBy('id', 'desc'); // fallback default

        $records = $query->paginate($perPage)
            ->appends($request->only(['vehicle_id','search','status','sort','direction','per_page']));

        return view('maintenance.index', compact('records', 'search', 'filter', 'sort', 'direction'));
    }

    // ---------- CREATE ----------
    // GET /maintenance/create
    public function create(Request $request)
    {
        $useApi = (bool) $request->query('use_api', false);

        if ($useApi) {
            // via API
            $resp = Http::get(url($this->vehicleApi));
            $list = $resp->ok() ? ($resp->json()['data'] ?? $resp->json() ?? []) : [];
        } else {
            // via internal service (no Vehicle::)
            $json = $this->vehicleService->all();
            $list = $json instanceof \Illuminate\Http\JsonResponse
                ? ($json->getData(true)['data'] ?? [])
                : (is_array($json) ? $json : []);
        }

        // only available vehicles
        $vehicles = collect($list)
            ->filter(fn ($v) => ($v['availability_status'] ?? '') === 'available')
            ->values();

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

        // 1) Vehicle
        if ($useApi) {
            $vehResp = Http::get(url($this->vehicleApi . '/' . $validated['vehicle_id']));
            if ($vehResp->failed()) {
                return back()->withErrors(['vehicle_id' => 'Vehicle not found'])->withInput();
            }
            $vehicle = $vehResp->json()['data'] ?? null;
        } else {
            $jsonResponse = $this->vehicleService->find($validated['vehicle_id']);
            $vehicle = $jsonResponse->getData(true)['data'] ?? null;
        }

        if (!$vehicle) {
            return back()->withErrors(['vehicle_id' => 'Vehicle not found'])->withInput();
        }

        // 2) Guards
        if (($vehicle['availability_status'] ?? '') !== 'available') {
            return back()->withErrors(['vehicle_id' => 'This vehicle is not available to schedule maintenance.'])->withInput();
        }

        if ($useApi) {
            $msResp = Http::get(url($this->vehicleApi . '/' . $validated['vehicle_id'] . '/maintenances'));
            $list = $msResp->json()['data'] ?? [];
        } else {
            $jsonResponse = $this->maintenanceService->allByVehicle($validated['vehicle_id']);
            $list = $jsonResponse->getData(true)['data'] ?? [];
        }

        $alreadyScheduled = collect($list)->contains(fn ($m) => ($m['status'] ?? '') === 'Scheduled');
        if ($alreadyScheduled) {
            return back()->withErrors(['vehicle_id' => 'This vehicle already has a scheduled maintenance.'])->withInput();
        }

        // 3) Create
        if ($useApi) {
            $payload = $validated + ['status' => 'Scheduled'];
            $resp = Http::post(url($this->maintenanceApi), $payload);
            if ($resp->failed()) {
                return back()->withErrors(['api' => 'Failed to create maintenance via API'])->withInput();
            }
        } else {
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
        }

        // 4) Update vehicle status — API vs Service (avoid self-call)
        if ($useApi) {
            Http::post(url($this->vehicleApi . '/update-status'), [
                'vehicle_id' => $validated['vehicle_id'],
                'status'     => 'under_maintenance',
            ]);
        } else {
            $this->vehicleService->updateStatus($validated['vehicle_id'], 'under_maintenance');
        }

        return redirect()->route('maintenance.index')->with('ok', 'Maintenance successfully scheduled.');
    }

    // ---------- EDIT FORM ----------
    public function edit(Maintenance $maintenance, Request $request)
    {
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

        $toStatus   = $request->status;
        $fromStatus = $maintenance->status;

        if ($useApi) {
            // API path
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

            // Vehicle via API
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
        } else {
            // Service path
            DB::transaction(function () use ($request, $maintenance, $fromStatus, $toStatus) {
                $maintenance->fill([
                    'maintenance_type' => $request->maintenance_type,
                    'service_date'     => $request->service_date,
                    'status'           => $toStatus,
                    'cost'             => $request->cost,
                    'notes'            => $request->notes,
                ]);

                if ($fromStatus !== 'Completed' && $toStatus === 'Completed') {
                    $maintenance->completed_at = now();
                } elseif ($fromStatus === 'Completed' && $toStatus !== 'Completed') {
                    $maintenance->completed_at = null;
                }

                $maintenance->save();
            });

            // Vehicle via Service (no HTTP self-call)
            if (in_array($toStatus, ['Completed', 'Cancelled'])) {
                $this->vehicleService->updateStatus($maintenance->vehicle_id, 'available');
            } elseif ($toStatus === 'Scheduled') {
                $this->vehicleService->updateStatus($maintenance->vehicle_id, 'under_maintenance');
            }
        }

        return redirect()->route('maintenance.index')->with('ok', 'Maintenance record successfully updated.');
    }

    // ---------- DELETE ----------
    // DELETE /maintenance/{maintenance}
    public function destroy(Maintenance $maintenance)
    {
        $useApi = (bool) request()->query('use_api', false);
        $vehicleId    = $maintenance->vehicle_id;
        $wasScheduled = $maintenance->status === 'Scheduled';

        if ($useApi) {
            // API path
            $resp = Http::delete(url($this->maintenanceApi . '/' . $maintenance->id));
            if ($resp->failed()) {
                return back()->withErrors(['api' => 'Failed to delete maintenance via API']);
            }

            if ($wasScheduled) {
                Http::post(url($this->vehicleApi . '/update-status'), [
                    'vehicle_id' => $vehicleId,
                    'status'     => 'available',
                ]);
            }
        } else {
            // Service path
            $maintenance->delete();

            if ($wasScheduled) {
                $stillScheduled = Maintenance::where('vehicle_id', $vehicleId)
                    ->where('status', 'Scheduled')
                    ->exists();

                if (!$stillScheduled) {
                    $this->vehicleService->updateStatus($vehicleId, 'available');
                }
            }
        }

        return redirect()->route('maintenance.index')->with('ok', 'Maintenance record successfully deleted');
    }
}