<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Maintenance;

class MaintenanceApiController extends Controller
{
    /**
     * GET /api/maintenances
     */
    public function index(Request $request)
    {
        return response()->json(Maintenance::orderByDesc('service_date')->get());
    }

    /**
     * GET /api/vehicles/{vehicle}/maintenances
     * Convenience: all maintenances for a vehicle
     */
    public function byVehicle(int $vehicle)
    {
        $all = Maintenance::where('vehicle_id', $vehicle)
            ->orderByDesc('service_date')
            ->get();

        return response()->json($all);
    }

    /**
     * GET /api/maintenances/{id}
     */
    public function show(int $id)
    {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }
        return response()->json($m);
    }

    /**
     * POST /api/maintenances
     * Create. Uses your real column names.
     * Status default = "Scheduled"
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id'       => 'required|integer|exists:vehicles,id',
            'maintenance_type' => 'required|string|max:50',
            'service_date'     => 'required|date',
            'cost'             => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string|max:1000',
            'status'           => 'sometimes|string|in:Scheduled,Completed,Cancelled',
            'completed_at'     => 'sometimes|nullable|date',
        ]);

        // force default if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'Scheduled';
        }

        $m = Maintenance::create($data);

        // if status came in as Completed/Cancelled at creation, let State set side-effects
        // (completed_at, vehicle state, etc.)
        if (in_array($m->status, ['Completed','Cancelled'])) {
            // normalize via state so completed_at etc. is set consistently
            $m->transitionTo($m->status);
        }

        return response()->json($m, 201);
    }

    /**
     * PUT /api/maintenances/{id}
     * Update normal fields; if "status" present, switch via State pattern.
     */
    public function update(Request $request, int $id)
    {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }

        $validated = $request->validate([
            'vehicle_id'       => 'sometimes|integer|exists:vehicles,id',
            'maintenance_type' => 'sometimes|string|max:50',
            'service_date'     => 'sometimes|date',
            'cost'             => 'sometimes|numeric|min:0',
            'notes'            => 'sometimes|nullable|string|max:1000',
            'status'           => 'sometimes|string|in:Scheduled,Completed,Cancelled',
            'completed_at'     => 'sometimes|nullable|date',
        ]);

        // 1) pull out status (if any) to handle via State
        $newStatus = $validated['status'] ?? null;
        unset($validated['status']);

        // 2) update other fields first
        if (!empty($validated)) {
            $m->update($validated);
        }

        // 3) if status provided AND different, do a safe transition
        if ($newStatus && $newStatus !== $m->status) {
            $m = $m->transitionTo($newStatus); // State pattern call
        }

        return response()->json($m);
    }

    /**
     * DELETE /api/maintenances/{id}
     */
    public function destroy(int $id)
    {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }

        $m->delete();
        return response()->json(['message' => 'Maintenance deleted successfully']);
    }
}