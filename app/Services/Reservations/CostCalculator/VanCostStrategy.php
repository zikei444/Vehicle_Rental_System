<?php

namespace App\Services\Reservations\CostCalculator;

class VanCostStrategy implements CostStrategy
{
    public function calculate($vehicle, $days)
    {
        //insurance fee RM50
        return ($vehicle['rental_price'] * $days) + 50;
    }
}