<?php

// STUDENT NAME: Loh Yun Le
// STUDENT ID: 23WMR14583

namespace App\Services;

use App\Models\Maintenance;
use Illuminate\Http\JsonResponse;
use App\Services\States\Maintenance\Scheduled;
use App\Services\States\Maintenance\Completed;
use App\Services\States\Maintenance\Cancelled;
use App\Services\States\Maintenance\MaintenanceStatus;


class MaintenanceService
{
    // Get all maintenances for one vehicle
    public function allByVehicle(int $vehicleId): JsonResponse
    {
        $records = Maintenance::where('vehicle_id', $vehicleId)
            ->orderBy('service_date', 'desc')
            ->get()
            ->map(fn ($m) => $this->formatMaintenance($m));

        return response()->json(['data' => $records]);
    }

    // Get maintenance by id
    public function find(int $id): JsonResponse
    {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }
        return response()->json(['data' => $this->formatMaintenance($m)]);
    }

    // CREATE a Scheduled maintenance
    public function create(array $data): JsonResponse
    {
        $m = new Maintenance([
            'vehicle_id'       => $data['vehicle_id'],
            'maintenance_type' => $data['maintenance_type'],
            'service_date'     => $data['service_date'],
            'cost'             => $data['cost'],
            'notes'            => $data['notes'] ?? null,
        ]);

        // Use state class to set and update Scheduled
        $m = (new Scheduled())->schedule($m);

        return response()->json(['data' => $this->formatMaintenance($m)], 201);
    }

    // UPDATE a Scheduled maintenance
    public function update(int $id, array $data): JsonResponse
    {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }

        $current = ucfirst(strtolower((string) $m->status));
        $requested = isset($data['status']) ? ucfirst(strtolower((string) $data['status'])) : $current;

        $isTerminal = in_array($current, ['Completed','Cancelled'], true);

        // If current record is completed/cancelled, lock the status
        if ($isTerminal) {
            $requested = $current; 
        }

        // Update basic fields
        $m->fill([
            'maintenance_type' => $data['maintenance_type'] ?? $m->maintenance_type,
            'service_date'     => $data['service_date']     ?? $m->service_date,
            'cost'             => $data['cost']             ?? $m->cost,
            'notes'            => array_key_exists('notes', $data) ? $data['notes'] : $m->notes,
        ]);
        $m->save();

        // Change status only when allowed
        if ($requested !== $current && !$isTerminal) {
            $state = $this->stateFor($m);
            $m = $state->transitionTo($m, $requested);
        }

        return response()->json(['data' => $this->formatMaintenance($m)]);
    }


    // DELETE maintenance record
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

    // Get the state class for the current status
    private function stateFor(Maintenance $m): MaintenanceStatus
    {
        $status = ucfirst(strtolower((string) $m->status));
        return match ($status) {
            'Scheduled' => new Scheduled(),
            'Completed' => new Completed(),
            'Cancelled' => new Cancelled(),
            default     => new Scheduled(), 
        };
    }
}