<?php

namespace App\States\Vehicle;

class AvailableState extends VehicleState
{
    public function getName(): string
    {
        return 'Available';
    }

    public function markAsUnderMaintenance()
    {
        $this->vehicle->availability_status = 'Under Maintenance';
        $this->vehicle->save();
        $this->vehicle->setState(new UnderMaintenanceState($this->vehicle));
    }
}
