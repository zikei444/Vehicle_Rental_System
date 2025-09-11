<?php

namespace App\Services\States\Maintenance;

use App\Models\Maintenance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * "Scheduled" state:
 * - Can move to Completed or Cancelled.
 * - Also used for initial scheduling (creation) via schedule().
 */
class Scheduled extends BaseStatus
{
    public function name(): string { return 'Scheduled'; }

    /** Allowed transitions from Scheduled. */
    public function transitionTo(Maintenance $m, string $newStatus): Maintenance
    {
        return match ($newStatus) {
            'Completed' => $this->setAndSave($m, 'Completed'),
            'Cancelled' => $this->setAndSave($m, 'Cancelled'),
            default     => $this->fail("From Scheduled you can only go to Completed or Cancelled."),
        };
    }

    /**
     * Initial scheduling flow with duplicate guard:
     * 1) Lock the vehicle row
     * 2) Ensure no other "Scheduled" exists for this vehicle
     * 3) Save "Scheduled" and flip vehicle to under_maintenance
     */
    public function schedule(Maintenance $m): Maintenance
    {
        return DB::transaction(function () use ($m) {
            $vehicle = $m->vehicle()->lockForUpdate()->first();
            if (!$vehicle) {
                throw ValidationException::withMessages(['vehicle_id' => 'Vehicle not found.']);
            }

            if (($vehicle->availability_status ?? '') !== 'available') {
                throw ValidationException::withMessages(['vehicle_id' => 'Vehicle not available for scheduling.']);
            }

            $exists = Maintenance::where('vehicle_id', $m->vehicle_id)
                ->where('status', 'Scheduled')
                ->exists();
            if ($exists) {
                throw ValidationException::withMessages(['vehicle_id' => 'This vehicle already has a scheduled maintenance.']);
            }

            // Delegate persistence + vehicle sync to the base helper
            return $this->setAndSave($m, 'Scheduled');
        });
    }
}