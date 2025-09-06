<?php

namespace App\States\Vehicle;

use App\Models\Vehicle;

abstract class VehicleState
{
    protected $vehicle;

    public function __construct(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
    }

    abstract public function getName(): string;

    // default transitions (override if needed)
    public function markAsAvailable() {}
    public function markAsReserved() {}
    public function markAsRented() {}
    public function markAsUnderMaintenance() {}
}
