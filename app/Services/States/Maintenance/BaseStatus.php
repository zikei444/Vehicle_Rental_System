<?php

// STUDENT NAME: Loh Yun Le
// STUDENT ID: 23WMR14583

namespace App\Services\States\Maintenance;

use App\Models\Maintenance;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

abstract class BaseStatus implements MaintenanceStatus
{
    abstract public function name(): string;

    protected function fail(string $message): never
    {
        throw ValidationException::withMessages(['status' => $message]);
    }

    protected function setAndSave(Maintenance $m, string $to): Maintenance
    {
        return DB::transaction(function () use ($m, $to) {
            // Locks the vehicle to avoid race conditions.
            $vehicle = $m->vehicle()->lockForUpdate()->first();

            // Update status + completion time
            $m->status       = $to;
            $m->completed_at = ($to === 'Completed') ? now() : null;
            $m->save();

            // Keep vehicle availability in sync with maintenance status
            $vehicle->availability_status = ($to === 'Scheduled')
                ? 'under_maintenance'
                : 'available';
            $vehicle->save();

            return $m;
        });
    }

    // By default, blocks any status change unless the current state explicitly allows it
    public function transitionTo(Maintenance $m, string $newStatus): Maintenance
    {
        $this->fail("Transition from {$this->name()} to {$newStatus} is not allowed.");
    }
}