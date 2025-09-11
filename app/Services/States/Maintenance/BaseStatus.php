<?php

namespace App\Services\States\Maintenance;

use App\Models\Maintenance;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

/**
 * Base class for all Maintenance states.
 * Shared helpers live here; concrete states override transition rules.
 */
abstract class BaseStatus implements MaintenanceStatus
{
    /** Each state returns its own name (e.g. "Scheduled"). */
    abstract public function name(): string;

    /** Standard 422 error for invalid transitions. */
    protected function fail(string $message): never
    {
        throw ValidationException::withMessages(['status' => $message]);
    }

    /**
     * Apply a status change AND sync vehicle availability in one DB transaction.
     * Locks the related vehicle first to avoid race conditions.
     */
    protected function setAndSave(Maintenance $m, string $to): Maintenance
    {
        return DB::transaction(function () use ($m, $to) {
            // Serialize concurrent transitions on the same vehicle
            $vehicle = $m->vehicle()->lockForUpdate()->first();

            // Update maintenance state + completion timestamp
            $m->status       = $to;
            $m->completed_at = ($to === 'Completed') ? now() : null;
            $m->save();

            // Keep vehicle availability in sync with maintenance state
            $vehicle->availability_status = ($to === 'Scheduled')
                ? 'under_maintenance'
                : 'available';
            $vehicle->save();

            return $m;
        });
    }

    /**
     * Default rule: no transitions unless a child class allows it.
     * (Scheduled/Completed/Cancelled override this.)
     */
    public function transitionTo(Maintenance $m, string $newStatus): Maintenance
    {
        $this->fail("Transition from {$this->name()} to {$newStatus} is not allowed.");
    }
}