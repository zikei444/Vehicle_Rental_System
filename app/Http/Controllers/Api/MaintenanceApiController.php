<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Maintenance;

/**
 * Maintenance REST API
 * - index(): filters + search + sort + pagination → { data, meta }
 * - byVehicle(): convenience listing for a single vehicle
 */
class MaintenanceApiController extends Controller
{
    // filter, search, sort, pagination
    public function index(Request $request)
    {
        $q = Maintenance::query()->with('vehicle');

        // ----- Filters -----
        if ($request->filled('vehicle_id')) {
            $q->where('vehicle_id', (int) $request->query('vehicle_id'));
        }
        if ($request->filled('status') && in_array($request->status, ['Scheduled','Completed','Cancelled'], true)) {
            $q->where('status', $request->status);
        }

        // ----- Search (maintenance + vehicle fields) -----
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

        // ----- Sort (allow-list to avoid unsafe columns) -----
        $sort = $request->query('sort', 'service_date');
        $dir  = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['service_date','cost','status','created_at'];
        if (!in_array($sort, $allowedSorts, true)) $sort = 'service_date';
        $q->orderBy($sort, $dir);

        // ----- Pagination -----
        $per  = (int) $request->query('per_page', 10);
        $per  = ($per > 0 && $per <= 100) ? $per : 10;
        $page = max((int) $request->query('page', 1), 1);
        $p    = $q->paginate($per, ['*'], 'page', $page);

        // Consistent payload for UIs/clients
        return response()->json([
            'data' => $p->items(),
            'meta' => [
                'total'        => $p->total(),
                'per_page'     => $p->perPage(),
                'current_page' => $p->currentPage(),
                'last_page'    => $p->lastPage(),
                'sort'         => $sort,
                'direction'    => $dir,
                'filters'      => [
                    'vehicle_id' => $request->query('vehicle_id'),
                    'status'     => $request->query('status'),
                    'search'     => $request->query('search'),
                ],
            ],
        ]);
    }

    /** GET /api/vehicles/{vehicleId}/maintenances – convenience wrapper around index(). */
    public function byVehicle(Request $request, int $vehicleId)
    {
        $request->merge(['vehicle_id' => $vehicleId]);
        return $this->index($request);
    }

    /** Delegate updates to the service to enforce state transitions & errors. */
    public function update(Request $request, int $id)
    {
        // (Add your request validation here if needed)
        return app(\App\Services\MaintenanceService::class)->update(
            $id,
            $request->only('maintenance_type', 'service_date', 'status', 'cost', 'notes')
        );
    }
}