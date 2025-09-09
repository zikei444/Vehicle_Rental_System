<?php

namespace App\Services\Maintenance;

use App\Models\Maintenance;
use Illuminate\Validation\ValidationException;

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
     * Update the Maintenance model’s status and set side-effects (e.g., completed_at).
     */
    protected function setAndSave(Maintenance $m, string $status): Maintenance {
        $m->status = $status;
        if ($status === 'Completed' && is_null($m->completed_at)) {
            $m->completed_at = now();
        }
        $m->save();
        return $m;
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