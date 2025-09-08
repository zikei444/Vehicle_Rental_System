<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MaintenanceService;
use App\Models\Maintenance; // only used for index pagination (lightweight)

class MaintenanceApiController extends Controller
{
    protected MaintenanceService $service;

    public function __construct(MaintenanceService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/maintenances
     * Return paginated maintenances (for admin dashboards)
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        // light pagination directly; the rest use the service
        $page = Maintenance::orderByDesc('service_date')
            ->paginate($perPage);

        // if you prefer to keep *everything* in the service layer,
        // we can move this into the service later.
        return response()->json([
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
                'last_page'    => $page->lastPage(),
            ]
        ]);
    }

    /**
     * GET /api/vehicles/{vehicle}/maintenances
     * Convenience endpoint: all maintenances by vehicle
     */
    public function byVehicle(int $vehicle)
    {
        // calls service->allByVehicle(), which returns JsonResponse
        return $this->service->allByVehicle($vehicle);
    }

    /**
     * GET /api/maintenances/{id}
     */
    public function show(int $id)
    {
        return $this->service->find($id);
    }

    /**
     * POST /api/maintenances
     */
    public function store(Request $request)
    {
        // basic validation — tweak rules to match your schema
        $data = $request->validate([
            'vehicle_id'   => ['required','integer','exists:vehicles,id'],
            'type'         => ['required','string','max:50'],   // routine/repair/inspection
            'status'       => ['required','string','max:50'],   // scheduled/in_progress/completed
            'service_date' => ['required','date'],
            'completed_at' => ['nullable','date'],
            'cost'         => ['nullable','numeric','min:1'],
            'notes'        => ['nullable','string','max:500'],
        ]);

        return $this->service->create($data);
    }

    /**
     * PUT /api/maintenances/{id}
     */
    public function update(int $id, Request $request)
    {
        $data = $request->validate([
            'vehicle_id'   => ['sometimes','integer','exists:vehicles,id'],
            'type'         => ['sometimes','string','max:50'],
            'status'       => ['sometimes','string','max:50'],
            'service_date' => ['sometimes','date'],
            'completed_at' => ['sometimes','nullable','date'],
            'cost'         => ['sometimes','numeric','min:0'],
            'notes'        => ['sometimes','nullable','string','max:1000'],
        ]);

        return $this->service->update($id, $data);
    }

    /**
     * DELETE /api/maintenances/{id}
     */
    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
