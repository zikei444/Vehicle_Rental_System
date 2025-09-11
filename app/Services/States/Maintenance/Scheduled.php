<?php

namespace App\Services\States\Maintenance;

use App\Models\Maintenance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Scheduled extends BaseStatus
{
    public function name(): string { return 'Scheduled'; }

    /**
     * Allowed transitions from Scheduled to Completed/Cancelled.
     */
    public function transitionTo(Maintenance $m, string $newStatus): Maintenance
    {
        return match ($newStatus) {
            'Completed' => $this->setAndSave($m, 'Completed'),
            'Cancelled' => $this->setAndSave($m, 'Cancelled'),
            default     => $this->fail("From Scheduled you can only go to Completed or Cancelled."),
        };
    }

    /**
     * Schedule (creation or re-schedule) with a hard guard to prevent duplicates.
     * Locks the vehicle row first, then checks for existing Scheduled records.
     */
    public function schedule(Maintenance $m): Maintenance
    {
        return DB::transaction(function () use ($m) {
            // 1) Lock vehicle row to serialize competing schedules on the same vehicle
            $vehicle = $m->vehicle()->lockForUpdate()->first();

            if (!$vehicle) {
                throw ValidationException::withMessages(['vehicle_id' => 'Vehicle not found.']);
            }

            // 2) If vehicle not available, block (optional but nice)
            if (($vehicle->availability_status ?? '') !== 'available') {
                throw ValidationException::withMessages(['vehicle_id' => 'This vehicle is not available to schedule maintenance.']);
            }

            // 3) Guard: ensure no other Scheduled exists now that we hold the vehicle lock
            $exists = Maintenance::where('vehicle_id', $m->vehicle_id)
                ->where('status', 'Scheduled')
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages(['vehicle_id' => 'This vehicle already has a scheduled maintenance.']);
            }

            // 4) Save the maintenance as Scheduled and flip vehicle
            $m->status       = 'Scheduled';
            $m->completed_at = null;
            $m->save();

            $vehicle->availability_status = 'under_maintenance';
            $vehicle->save();

            return $m;
        });
    }
}