<?php

namespace App\Services;

use App\Models\Maintenance;
use Illuminate\Http\JsonResponse;
use App\Services\States\Maintenance\Scheduled;

class MaintenanceService
{
    /**
     * Return all maintenance records for a vehicle in JSON
     */
    public function allByVehicle(int $vehicleId): JsonResponse
    {
        $records = Maintenance::where('vehicle_id', $vehicleId)
            ->orderBy('service_date', 'desc')
            ->get()
            ->map(fn ($m) => $this->formatMaintenance($m));

        return response()->json(['data' => $records]);
    }

    /**
     * Return single maintenance by ID as JSON
     */
    public function find(int $id): JsonResponse
    {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }
        return response()->json(['data' => $this->formatMaintenance($m)]);
    }

    /**
     * Create a maintenance record (Scheduled) and flip vehicle via state.
     */
    public function create(array $data): JsonResponse
    {
        // Build entity (no status yet; state sets it + flips vehicle)
        $m = new Maintenance([
            'vehicle_id'       => $data['vehicle_id'],
            'maintenance_type' => $data['maintenance_type'],
            'service_date'     => $data['service_date'],
            'cost'             => $data['cost'],
            'notes'            => $data['notes'] ?? null,
        ]);

        $m = (new Scheduled())->schedule($m);

        return response()->json(['data' => $this->formatMaintenance($m)], 201);
    }

    /**
     * Update fields; if status changes, transition via state.
     */
    public function update(int $id, array $data): JsonResponse
    {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }

        // Update non-status fields first
        $m->fill([
            'maintenance_type' => $data['maintenance_type'] ?? $m->maintenance_type,
            'service_date'     => $data['service_date']     ?? $m->service_date,
            'cost'             => $data['cost']             ?? $m->cost,
            'notes'            => array_key_exists('notes', $data) ? $data['notes'] : $m->notes,
        ]);
        $m->save();

        // Transition if status provided & different
        if (isset($data['status']) && $data['status'] !== $m->status) {
            if ($m->status === 'Scheduled') {
                $m = (new Scheduled())->transitionTo($m, $data['status']);
            } else {
                // If you later add other states, branch here
                return response()->json(['error' => 'Transition not allowed from current state'], 422);
            }
        }

        return response()->json(['data' => $this->formatMaintenance($m)]);
    }

    /**
     * Delete maintenance by ID and return JSON
     * (Delete isn’t a state transition; keep your controller’s post-delete handling if needed.)
     */
    public function delete(int $id): JsonResponse
    {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }
        $m->delete();
        return response()->json(['status' => 'success', 'message' => 'Maintenance deleted']);
    }

    private function formatMaintenance(Maintenance $m): array
    {
        return [
            'id'               => $m->id,
            'vehicle_id'       => $m->vehicle_id,
            'maintenance_type' => $m->maintenance_type,
            'status'           => $m->status,
            'service_date'     => $m->service_date?->toDateString(),
            'completed_at'     => $m->completed_at?->toDateTimeString(),
            'created_at'       => $m->created_at?->toDateTimeString(),
            'updated_at'       => $m->updated_at?->toDateTimeString(),
            'cost'             => $m->cost,
            'notes'            => $m->notes,
        ];
    }
}