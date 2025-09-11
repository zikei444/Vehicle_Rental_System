<?php

namespace App\Services\States\Vehicle;

use App\Models\Vehicle;

abstract class VehicleState {
    protected $vehicle;

    public function __construct(Vehicle $vehicle) {
        $this->vehicle = $vehicle;
    }

    abstract public function getName(): string;

    public function markAsAvailable(): void {}
    public function markAsUnderMaintenance(): void {}
}
