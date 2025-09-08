<?php

namespace App\Services;

use App\Models\Maintenance;
use Illuminate\Http\JsonResponse;

class MaintenanceService {
    /**
     * Return all maintenance records for a vehicle in JSON
     */
    public function allByVehicle(int $vehicleId): JsonResponse {
        $records = Maintenance::where('vehicle_id', $vehicleId)
            ->orderBy('service_date', 'desc')
            ->get()
            ->map(fn($m) => $this->formatMaintenance($m));

        return response()->json(['data' => $records]);
    }

    /**
     * Return single maintenance by ID as JSON
     */
    public function find(int $id): JsonResponse {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }
        return response()->json(['data' => $this->formatMaintenance($m)]);
    }

    /**
     * Create a maintenance record and return as JSON
     */
    public function create(array $data): JsonResponse {
        $m = Maintenance::create($data);
        return response()->json(['data' => $this->formatMaintenance($m)], 201);
    }

    /**
     * Update maintenance by ID and return as JSON
     */
    public function update(int $id, array $data): JsonResponse {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }
        $m->update($data);
        return response()->json(['data' => $this->formatMaintenance($m)]);
    }

    /**
     * Delete maintenance by ID and return JSON
     */
    public function delete(int $id): JsonResponse {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }
        $m->delete();
        return response()->json(['status' => 'success', 'message' => 'Maintenance deleted']);
    }

    /**
     * Format maintenance for JSON response
     */
    private function formatMaintenance(Maintenance $m): array {
        // Add relations if you have them, for example $m->vehicle
        return [
            'id'                => $m->id,
            'vehicle_id'        => $m->vehicle_id,
            'maintenance_type'  => $m->maintenance_type,        // routine, repair, inspection
            'status'            => $m->status,                 // scheduled, in_progress, completed, cancelled
            'service_date'      => $m->service_date?->toDateString(),
            'completed_at'      => $m->completed_at?->toDateTimeString(),
            'created_at'        => $m->created_at?->toDateTimeString(),
            'updated_at'        => $m->updated_at?->toDateTimeString(),
            'cost'              => $m->cost,
            'notes'             => $m->notes,
        ];
    }
}
