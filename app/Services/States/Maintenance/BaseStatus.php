<?php

// STUDENT NAME: Loh Yun Le
// STUDENT ID: 23WMR14583

namespace App\Services\States\Maintenance;

use App\Models\Maintenance;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

abstract class BaseStatus implements MaintenanceStatus
{
    // Every status must give its name
    abstract public function name(): string;

    // Throw error if status change is not allowed
    protected function fail(string $message): never
    {
        throw ValidationException::withMessages(['status' => $message]);
    }

    // Save new status and update vehicle availability
    protected function setAndSave(Maintenance $m, string $to): Maintenance
    {
        return DB::transaction(function () use ($m, $to) {
            // Locks the vehicle to avoid race conditions.
            $vehicle = $m->vehicle()->lockForUpdate()->first();

            // Update status & completion time
            $m->status       = $to;
            $m->completed_at = ($to === 'Completed') ? now() : null;
            $m->save();

            // Change vehicle state
            if ($to === 'Scheduled') {
                $vehicle->getState()->markAsUnderMaintenance();
            } else {
                $vehicle->getState()->markAsAvailable();
            }

            return $m;
        });
    }

    // By default, blocks any status change unless the current state explicitly allows it
    public function transitionTo(Maintenance $m, string $newStatus): Maintenance
    {
        $this->fail("Transition from {$this->name()} to {$newStatus} is not allowed.");
    }
}