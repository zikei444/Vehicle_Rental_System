<?php

namespace App\Services\Reservations\CostCalculator;

class TruckCostStrategy implements CostStrategy
{
    public function calculate($vehicle, $days)
    {
        //10% extra fee need to be charge
        return ($vehicle['rental_price'] * $days) * 1.1;
    }
}