<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Maintenance;

class MaintenanceApiController extends Controller
{
    /**
     * GET /api/maintenances
     * Supports filters, search, sorting, and pagination.
     * Returns a consistent { data, meta } payload.
     */
    public function index(Request $request)
    {
        $query = Maintenance::query()->with(['vehicle']);

        // ---- Filters ----
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', (int) $request->query('vehicle_id'));
        }

        if ($request->filled('status')) {
            // Accept only known statuses
            $status = $request->query('status');
            if (in_array($status, ['Scheduled', 'Completed', 'Cancelled'], true)) {
                $query->where('status', $status);
            }
        }

        // ---- Search (maintenance_type, notes, and vehicle fields) ----
        if ($request->filled('search')) {
            $s = $request->query('search');
            $query->where(function ($q) use ($s) {
                $q->where('maintenance_type', 'like', "%{$s}%")
                  ->orWhere('notes', 'like', "%{$s}%")
                  ->orWhere('cost', 'like', "%{$s}%")
                  ->orWhereHas('vehicle', function ($v) use ($s) {
                      $v->where('brand', 'like', "%{$s}%")
                        ->orWhere('model', 'like', "%{$s}%")
                        ->orWhere('registration_number', 'like', "%{$s}%");
                  });
            });
        }

        // ---- Sorting ----
        $sort      = $request->query('sort', 'service_date');                // default
        $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['service_date', 'cost', 'status', 'created_at'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'service_date';
        }
        $query->orderBy($sort, $direction);

        // ---- Pagination ----
        $perPage = (int) $request->query('per_page', 10);
        $perPage = ($perPage > 0 && $perPage <= 100) ? $perPage : 10;
        $page    = max((int) $request->query('page', 1), 1);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // ---- Consistent shape ----
        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'total'         => $paginator->total(),
                'per_page'      => $paginator->perPage(),
                'current_page'  => $paginator->currentPage(),
                'last_page'     => $paginator->lastPage(),
                'sort'          => $sort,
                'direction'     => $direction,
                'filters'       => [
                    'vehicle_id' => $request->query('vehicle_id'),
                    'status'     => $request->query('status'),
                    'search'     => $request->query('search'),
                ],
            ],
        ]);
    }

    /**
     * Optional: GET /api/vehicles/{vehicleId}/maintenances
     * Convenience listing that reuses index() logic.
     */
    public function byVehicle(Request $request, int $vehicleId)
    {
        // Reuse index() by injecting vehicle_id into the query bag
        $request->merge(['vehicle_id' => $vehicleId]);
        return $this->index($request);
    }
}