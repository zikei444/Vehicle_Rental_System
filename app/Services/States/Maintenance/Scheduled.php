<?php

// STUDENT NAME: Loh Yun Le
// STUDENT ID: 23WMR14583

namespace App\Services\States\Maintenance;

use App\Models\Maintenance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Scheduled extends BaseStatus
{
    public function name(): string { return 'Scheduled'; }

    // Allow only change from Scheduled → Completed/Cancelled
    public function transitionTo(Maintenance $m, string $newStatus): Maintenance
    {
        return match ($newStatus) {
            'Completed' => $this->setAndSave($m, 'Completed'),
            'Cancelled' => $this->setAndSave($m, 'Cancelled'),
            default     => $this->fail("From Scheduled you can only go to Completed or Cancelled."),
        };
    }

    // Create a new Scheduled maintenance
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

            // Only allow one Scheduled maintenance per vehicle
            $exists = Maintenance::where('vehicle_id', $m->vehicle_id)
                ->where('status', 'Scheduled')
                ->exists();
            if ($exists) {
                throw ValidationException::withMessages(['vehicle_id' => 'This vehicle already has a scheduled maintenance.']);
            }

            return $this->setAndSave($m, 'Scheduled');
        });
    }
}