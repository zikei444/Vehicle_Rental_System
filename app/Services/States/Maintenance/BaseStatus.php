<?php

namespace App\Services\States\Maintenance;

use App\Models\Maintenance;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

/**
 * BaseStatus provides shared logic for all maintenance states.
 * - Holds the common helper methods for saving transitions.
 * - Blocks invalid transitions by default (child classes override).
 */

abstract class BaseStatus implements MaintenanceStatus {
    // Every concrete state must define its own name (e.g., "Scheduled")
    abstract public function name(): string;

    /**
     * Throw a standard validation error when a transition is not allowed.
     */
    protected function fail(string $message): never {
        throw ValidationException::withMessages(['status' => $message]);
    }

    /**
     * Persist a status change and update vehicle availability in one transaction.
     */
    protected function setAndSave(Maintenance $m, string $to): Maintenance {
        return DB::transaction(function () use ($m, $to) {
            $m->status = $to;
            $m->completed_at = ($to === 'Completed') ? now() : null;
            $m->save();

            // lock the vehicle row while we change it
            $vehicle = $m->vehicle()->lockForUpdate()->first();

            if ($to === 'Scheduled') {
                $vehicle->availability_status = 'under_maintenance';
            } else { // Completed or Cancelled
                $vehicle->availability_status = 'available';
            }
            $vehicle->save();

            return $m;
        });
    }

    /**
     * By default, no transitions are allowed unless overridden.
     * Concrete state classes (Scheduled, Completed, Cancelled)
     * override this to define valid transitions.
     */
    public function transitionTo(Maintenance $m, string $newStatus): Maintenance {
        $this->fail("Transition from {$this->name()} to {$newStatus} is not allowed.");
    }
}