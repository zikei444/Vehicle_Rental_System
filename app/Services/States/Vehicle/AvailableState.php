<?php

// STUDENT NAME: Loh Yun Le
// STUDENT ID: 23WMR14583

namespace App\Services\States\Vehicle;

use App\Models\Vehicle;

class AvailableState extends VehicleState {
    public function getName(): string { return Vehicle::AVAILABLE; }

    // Change vehicle status to Under Maintenance
    public function markAsUnderMaintenance(): void {
        $this->vehicle->availability_status = \App\Models\Vehicle::UNDER_MAINTENANCE;
        $this->vehicle->save();
        $this->vehicle->setState(new UnderMaintenanceState($this->vehicle));
    }
}