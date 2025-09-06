<?php

namespace App\States\Vehicle;

class UnderMaintenanceState extends VehicleState
{
    public function getName(): string
    {
        return 'Under Maintenance';
    }

    public function markAsAvailable()
    {
        $this->vehicle->availability_status = 'Available';
        $this->vehicle->save();
        $this->vehicle->setState(new AvailableState($this->vehicle));
    }
}
