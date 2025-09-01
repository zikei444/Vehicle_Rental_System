<?php

namespace App\Services\Reservations\CostCalculator;

class CarCostStrategy implements CostStrategy
{
    public function calculate($vehicle, $days)
    {
        return $vehicle['rental_price'] * $days;
    }
}