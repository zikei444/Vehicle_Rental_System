<?php

// STUDENT NAME: Loh Yun Le
// STUDENT ID: 23WMR14583

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

    // ---------- LIST ----------
    public function index(Request $request)
    {
        $vehicleId = $request->query('vehicle_id');
        $search    = $request->query('search');
        $filter    = $request->query('status');
        $sortParam = $request->query('sort');
        $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Only allow certain columns for sorting
        $allowedSorts = ['service_date', 'created_at', 'updated_at', 'cost'];
        $sort = in_array($sortParam, $allowedSorts, true) ? $sortParam : null;

        // Query records with filters
        $records = Maintenance::with('vehicle')
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', (int) $vehicleId))
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
            ->orderBy('id', 'desc')
            ->get();

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

        // Only show vehicles currently available
        $vehicles = collect($list)->filter(fn ($v) => ($v['availability_status'] ?? '') === 'available')->values();

        return view('maintenance.create', ['vehicles' => $vehicles]);
    }

    // ---------- STORE ----------
    public function store(Request $request)
    {
        $useApi = (bool) $request->query('use_api', false);

        // Input validation
        $validated = $request->validate([
            'vehicle_id'       => 'required|integer|exists:vehicles,id',
            'maintenance_type' => 'required|string|max:50',
            'service_date'     => 'required|date|after_or_equal:today',
            'cost'             => 'required|numeric|min:1',
            'notes'            => 'nullable|string|max:500',
        ]);

        // Check vehicle exists and is available
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

        // Check no existing scheduled record for the same vehicle
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

        // Create record via API or service
        if ($useApi) {
            $payload = $validated + ['status' => 'Scheduled'];
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
        $maintenance->load('vehicle'); 
        $terminal = in_array($maintenance->status, ['Completed','Cancelled'], true);
        return view('maintenance.edit', compact('maintenance', 'terminal'));
    }

    // ---------- UPDATE ----------
    public function update(Request $request, Maintenance $maintenance)
    {
        // Input validation
        $validated = $request->validate([
            'maintenance_type' => 'required|string|max:50',
            'service_date'     => 'required|date',
            'status'           => 'required|in:Scheduled,Completed,Cancelled',
            'cost'             => 'required|numeric|min:1',
            'notes'            => 'nullable|string',
        ]);

        $response = $this->maintenanceService->update(
            $maintenance->id,
            $request->only('maintenance_type','service_date','status','cost','notes')
        );

        if ($response->getStatusCode() >= 400) {
            $json = $response->getData(true) ?? [];
            $msg  = $json['errors']['status'][0] ?? $json['error'] ?? 'Failed to update maintenance';
            return back()->withErrors(['status' => $msg])->withInput();
        }

        return redirect()
            ->route('maintenance.index')
            ->with('ok', 'Maintenance record successfully updated.');
    }

    // ---------- DELETE ----------
    public function destroy(Maintenance $maintenance)
    {
        $maintenance->delete();

        return redirect()
            ->route('maintenance.index')
            ->with('ok', 'Maintenance record successfully deleted');
    }
}