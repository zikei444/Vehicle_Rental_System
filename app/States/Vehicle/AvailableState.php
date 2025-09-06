<?php

namespace App\States\Vehicle;

use App\Models\Vehicle;

class AvailableState extends VehicleState
{
    public function getName(): string { return Vehicle::AVAILABLE; }

    public function markAsUnderMaintenance(): void
    {
        $this->vehicle->availability_status = Vehicle::UNDER_MAINTENANCE;
        $this->vehicle->save();
        $this->vehicle->setState(new UnderMaintenanceState($this->vehicle));
    }
}