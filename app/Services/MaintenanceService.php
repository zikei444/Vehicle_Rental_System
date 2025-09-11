<?php

namespace App\Services;

use App\Models\Maintenance;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Services\States\Maintenance\Scheduled;
use App\Services\States\Maintenance\Completed;
use App\Services\States\Maintenance\Cancelled;
use App\Services\States\Maintenance\MaintenanceStatus;

/**
 * Maintenance service layer (no direct vehicle updates here).
 * State classes own the business rules and vehicle availability flips.
 */
class MaintenanceService
{
    /** List all maintenances for a vehicle (JSON). */
    public function allByVehicle(int $vehicleId): JsonResponse
    {
        $records = Maintenance::where('vehicle_id', $vehicleId)
            ->orderBy('service_date', 'desc')
            ->get()
            ->map(fn ($m) => $this->formatMaintenance($m));

        return response()->json(['data' => $records]);
    }

    /** Fetch one maintenance (JSON). */
    public function find(int $id): JsonResponse
    {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }
        return response()->json(['data' => $this->formatMaintenance($m)]);
    }

    /**
     * Create a Scheduled maintenance via state (also flips vehicle).
     * Controller passes validated input; state handles atomicity.
     */
    public function create(array $data): JsonResponse
    {
        // Build entity; status is set by the state method
        $m = new Maintenance([
            'vehicle_id'       => $data['vehicle_id'],
            'maintenance_type' => $data['maintenance_type'],
            'service_date'     => $data['service_date'],
            'cost'             => $data['cost'],
            'notes'            => $data['notes'] ?? null,
        ]);

        // Centralized rule + transaction + vehicle lock
        $m = (new Scheduled())->schedule($m);

        return response()->json(['data' => $this->formatMaintenance($m)], 201);
    }

    /**
     * Update editable fields; if status changes, delegate to the CURRENT state's transition.
     * This correctly enforces terminal states (Completed/Cancelled) and flips vehicle availability.
     */
    public function update(int $id, array $data): JsonResponse
    {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }

        // Normalize current status (handle any casing)
        $current = ucfirst(strtolower((string) $m->status));
        $requested = isset($data['status']) ? ucfirst(strtolower((string) $data['status'])) : $current;

        $isTerminal = in_array($current, ['Completed','Cancelled'], true);

        // If current record is terminal, silently lock status to the current one
        if ($isTerminal) {
            $requested = $current; // ignore any attempt to change status
        }

        // Update non-status fields
        $m->fill([
            'maintenance_type' => $data['maintenance_type'] ?? $m->maintenance_type,
            'service_date'     => $data['service_date']     ?? $m->service_date,
            'cost'             => $data['cost']             ?? $m->cost,
            'notes'            => array_key_exists('notes', $data) ? $data['notes'] : $m->notes,
        ]);
        $m->save();

        // If status is actually changing and the record is NOT terminal, go via state
        if ($requested !== $current && !$isTerminal) {
            // Delegate to the right state (Scheduled → Completed/Cancelled)
            $state = $this->stateFor($m);
            $m = $state->transitionTo($m, $requested);
        }

        // Return the latest version (status may be unchanged if terminal)
        return response()->json(['data' => $this->formatMaintenance($m)]);
    }


    /** Delete maintenance (no state transition). */
    public function delete(int $id): JsonResponse
    {
        $m = Maintenance::find($id);
        if (!$m) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }
        $m->delete();
        return response()->json(['status' => 'success', 'message' => 'Maintenance deleted']);
    }

    /** Standard JSON shape for this module. */
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

    /** Resolve the current state's object for a maintenance row (case-insensitive). */
    private function stateFor(Maintenance $m): MaintenanceStatus
    {
        $status = ucfirst(strtolower((string) $m->status)); // normalize "completed" → "Completed"
        return match ($status) {
            'Scheduled' => new Scheduled(),
            'Completed' => new Completed(),
            'Cancelled' => new Cancelled(),
            default     => new Scheduled(), // safe default
        };
    }
}