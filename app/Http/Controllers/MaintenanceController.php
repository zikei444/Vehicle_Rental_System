<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Maintenance;
use App\Services\MaintenanceService;
use App\Services\VehicleService;

/**
 * Web controller for Maintenance pages (Blade).
 * Note: Vehicle availability updates are handled ONLY by State classes.
 */
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

    /** Helper: get one maintenance via API or service (for UI rendering). */
    private function getMaintenanceJson(int $id, bool $useApi): ?array
    {
        if ($useApi) {
            $response = Http::timeout(10)->get(url($this->maintenanceApi . '/' . $id));
            if ($response->failed()) return null;
            return $response->json();
        }

        $jsonResponse = $this->maintenanceService->find($id);
        return $jsonResponse instanceof \Illuminate\Http\JsonResponse
            ? ($jsonResponse->getData(true)['data'] ?? null)
            : (is_array($jsonResponse) ? $jsonResponse : null);
    }

    // ---------- LIST ----------
    public function index(Request $request)
    {
        $useApi    = (bool) $request->query('use_api', false);
        $vehicleId = $request->query('vehicle_id');
        $perPage   = 10;

        $search    = $request->query('search');
        $filter    = $request->query('status');   // Scheduled, Completed, Cancelled
        $sortParam = $request->query('sort');
        $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Only allow known columns for sort to keep queries safe
        $allowedSorts = ['service_date', 'created_at', 'updated_at', 'cost'];
        $sort = in_array($sortParam, $allowedSorts, true) ? $sortParam : null;

        if ($useApi) {
            // Pull from API, then paginate in-memory for Blade
            $resp  = Http::get(url($this->maintenanceApi), $request->only(['vehicle_id','status','search','sort','direction','page','per_page']));
            $array = $resp->ok() ? (array) $resp->json() : [];
            $items = collect(isset($array['data']) ? $array['data'] : $array);

            // Extra UI filters (kept for compatibility with existing view)
            if ($vehicleId) $items = $items->where('vehicle_id', (int) $vehicleId);
            if ($search) {
                $needle = mb_strtolower($search);
                $items  = $items->filter(function ($r) use ($needle) {
                    $in = static fn($v) => str_contains(mb_strtolower((string) $v), $needle);
                    return $in($r['maintenance_type'] ?? '')
                        || $in($r['notes'] ?? '')
                        || $in($r['cost'] ?? '')
                        || $in($r['vehicle']['brand'] ?? '')
                        || $in($r['vehicle']['model'] ?? '')
                        || $in($r['vehicle']['registration_number'] ?? '');
                });
            }
            if ($filter) $items = $items->where('status', $filter);
            if ($sort)   $items = $items->sortBy([[$sort, $direction === 'asc' ? SORT_ASC : SORT_DESC]]);
            else         $items = $items->sortByDesc('id');

            // Paginate for Blade
            $page    = LengthAwarePaginator::resolveCurrentPage();
            $slice   = $items->slice(($page - 1) * $perPage, $perPage)->values();
            $records = new LengthAwarePaginator($slice, $items->count(), $perPage, $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('maintenance.index', compact('records', 'search', 'filter', 'sort', 'direction'));
        }

        // Direct DB path for listing
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
            ->orderBy('id', 'desc');

        $records = $query->paginate($perPage)
            ->appends($request->only(['vehicle_id','search','status','sort','direction','per_page']));

        return view('maintenance.index', compact('records', 'search', 'filter', 'sort', 'direction'));
    }

    // ---------- CREATE ----------
    public function create(Request $request)
    {
        $useApi = (bool) $request->query('use_api', false);

        // Get vehicles list via API or internal service
        if ($useApi) {
            $resp = Http::get(url($this->vehicleApi));
            $list = $resp->ok() ? ($resp->json()['data'] ?? $resp->json() ?? []) : [];
        } else {
            $json = $this->vehicleService->all();
            $list = $json instanceof \Illuminate\Http\JsonResponse
                ? ($json->getData(true)['data'] ?? [])
                : (is_array($json) ? $json : []);
        }

        // Only show available vehicles for scheduling
        $vehicles = collect($list)->filter(fn ($v) => ($v['availability_status'] ?? '') === 'available')->values();

        return view('maintenance.create', ['vehicles' => $vehicles]);
    }

    // ---------- STORE ----------
    public function store(Request $request)
    {
        $useApi = (bool) $request->query('use_api', false);

        // Basic validation (you chose to skip FormRequests for now)
        $validated = $request->validate([
            'vehicle_id'       => 'required|integer|exists:vehicles,id',
            'maintenance_type' => 'required|string|max:50',
            'service_date'     => 'required|date|after_or_equal:today',
            'cost'             => 'required|numeric|min:1',
            'notes'            => 'nullable|string|max:500',
        ]);

        // UX guard: vehicle must exist and be available
        if ($useApi) {
            $vehResp = Http::get(url($this->vehicleApi . '/' . $validated['vehicle_id']));
            if ($vehResp->failed()) return back()->withErrors(['vehicle_id' => 'Vehicle not found'])->withInput();
            $vehicle = $vehResp->json()['data'] ?? null;
        } else {
            $jsonResponse = $this->vehicleService->find($validated['vehicle_id']);
            $vehicle = $jsonResponse->getData(true)['data'] ?? null;
        }
        if (!$vehicle) return back()->withErrors(['vehicle_id' => 'Vehicle not found'])->withInput();
        if (($vehicle['availability_status'] ?? '') !== 'available') {
            return back()->withErrors(['vehicle_id' => 'This vehicle is not available to schedule maintenance.'])->withInput();
        }

        // UX guard: ensure no other "Scheduled" exists (state re-checks inside DB tx)
        if ($useApi) {
            $msResp = Http::get(url($this->vehicleApi . '/' . $validated['vehicle_id'] . '/maintenances'));
            $list   = $msResp->json()['data'] ?? [];
        } else {
            $jsonResponse = $this->maintenanceService->allByVehicle($validated['vehicle_id']);
            $list   = $jsonResponse->getData(true)['data'] ?? [];
        }
        $alreadyScheduled = collect($list)->contains(fn ($m) => ($m['status'] ?? '') === 'Scheduled');
        if ($alreadyScheduled) {
            return back()->withErrors(['vehicle_id' => 'This vehicle already has a scheduled maintenance.'])->withInput();
        }

        // Create via API or service (state layer handles vehicle flip)
        if ($useApi) {
            $payload = $validated + ['status' => 'Scheduled']; // server should enforce status anyway
            $resp = Http::post(url($this->maintenanceApi), $payload);
            if ($resp->failed()) return back()->withErrors(['api' => 'Failed to create maintenance via API'])->withInput();
        } else {
            $response = $this->maintenanceService->create($validated);
            if ($response->getStatusCode() !== 201) {
                return back()->withErrors(['api' => 'Failed to create maintenance'])->withInput();
            }
        }

        return redirect()->route('maintenance.index')->with('ok', 'Maintenance successfully scheduled.');
    }

    // ---------- EDIT ----------
    public function edit(Maintenance $maintenance, Request $request)
    {
        $maintenance->load('vehicle'); // eager load for the view

        // Compute terminal in controller and pass to view
        $terminal = in_array($maintenance->status, ['Completed','Cancelled'], true);

        return view('maintenance.edit', compact('maintenance', 'terminal'));
    }

    // ---------- UPDATE ----------
    public function update(Request $request, Maintenance $maintenance)
    {
        $useApi = (bool) $request->query('use_api', false);

        // Basic rules (you skipped FormRequest)
        $request->validate([
            'maintenance_type' => 'required|string|max:50',
            'service_date'     => 'required|date',
            'status'           => 'required|in:Scheduled,Completed,Cancelled',
            'cost'             => 'required|numeric|min:1',
            'notes'            => 'nullable|string',
        ]);

        // Route through API or service; state layer will enforce transitions
        if ($useApi) {
            $payload = $request->only('maintenance_type','service_date','status','cost','notes');
            $resp = Http::put(url($this->maintenanceApi . '/' . $maintenance->id), $payload);

            if ($resp->failed()) {
                $json = $resp->json() ?? [];
                // Prefer the precise validation message returned by the state (ValidationException)
                $msg = $json['errors']['status'][0] ?? $json['error'] ?? 'Failed to update maintenance via API';
                return back()->withErrors(['status' => $msg])->withInput();
            }

        } else {
            $response = $this->maintenanceService->update(
                $maintenance->id,
                $request->only('maintenance_type','service_date','status','cost','notes')
            );

            if ($response->getStatusCode() >= 400) {
                $json = $response->getData(true) ?? [];
                // Prefer the exact validation message from state->fail()
                $msg = $json['errors']['status'][0] ?? $json['error'] ?? 'Failed to update maintenance';
                return back()->withErrors(['status' => $msg])->withInput();
            }
        }

        return redirect()->route('maintenance.index')->with('ok', 'Maintenance record successfully updated.');
    }

    // ---------- DELETE ----------
    public function destroy(Maintenance $maintenance)
    {
        $useApi = (bool) request()->query('use_api', false);

        if ($useApi) {
            // If API implementation frees vehicle for scheduled deletes, this is enough.
            $resp = Http::delete(url($this->maintenanceApi . '/' . $maintenance->id));
            if ($resp->failed()) return back()->withErrors(['api' => 'Failed to delete maintenance']);
        } else {
            // Local delete (no state change)
            $maintenance->delete();
            // Optional: free the vehicle if no other Scheduled remains (kept as-is to match your current behavior)
        }

        return redirect()->route('maintenance.index')->with('ok', 'Maintenance record successfully deleted');
    }
}