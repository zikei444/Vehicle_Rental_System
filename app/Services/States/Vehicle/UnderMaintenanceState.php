<?php

// STUDENT NAME: Loh Yun Le
// STUDENT ID: 23WMR14583

namespace App\Services\States\Vehicle;

use App\Models\Vehicle;

class UnderMaintenanceState extends VehicleState
{
    public function getName(): string { return Vehicle::UNDER_MAINTENANCE; }

    public function markAsAvailable(): void {
        $this->vehicle->availability_status = \App\Models\Vehicle::AVAILABLE;
        $this->vehicle->save();
        $this->vehicle->setState(new AvailableState($this->vehicle));
    }
}
