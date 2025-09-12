<?php

// STUDENT NAME: Loh Yun Le
// STUDENT ID: 23WMR14583

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Maintenance;

class MaintenanceApiController extends Controller
{
    // List maintenances with filter, search, sort, and pagination
    public function index(Request $request)
    {
        $q = Maintenance::query()->with('vehicle');

        if ($request->filled('vehicle_id')) {
            $q->where('vehicle_id', (int) $request->query('vehicle_id'));
        }

        if ($request->filled('status') && in_array($request->status, ['Scheduled','Completed','Cancelled'], true)) {
            $q->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($w) use ($s) {
                $w->where('maintenance_type', 'like', "%{$s}%")
                  ->orWhere('notes', 'like', "%{$s}%")
                  ->orWhere('cost', 'like', "%{$s}%")
                  ->orWhereHas('vehicle', function ($v) use ($s) {
                      $v->where('brand', 'like', "%{$s}%")
                        ->orWhere('model', 'like', "%{$s}%")
                        ->orWhere('registration_number', 'like', "%{$s}%");
                  });
            });
        }

        $sort = $request->query('sort', 'service_date');
        $dir  = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['service_date','cost','status','created_at'];
        if (!in_array($sort, $allowedSorts, true)) $sort = 'service_date';
        $q->orderBy($sort, $dir);
        $items = $q->get();

        return response()->json([
            'data' => $items,
            'meta' => [
                'total'    => $items->count(),
                'sort'     => $sort,
                'direction'=> $dir,
                'filters'  => [
                    'vehicle_id' => $request->query('vehicle_id'),
                    'status'     => $request->query('status'),
                    'search'     => $request->query('search'),
                ],
            ],
        ]);

    }

    // List maintenances for a specific vehicle
    public function byVehicle(Request $request, int $vehicleId)
    {
        $request->merge(['vehicle_id' => $vehicleId]);
        return $this->index($request);
    }

    // Update maintenance record through service layer
    public function update(Request $request, int $id)
    {
        return app(\App\Services\MaintenanceService::class)->update(
            $id,
            $request->only('maintenance_type', 'service_date', 'status', 'cost', 'notes')
        );
    }
}