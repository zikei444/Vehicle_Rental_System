<?php

namespace App\Services\Reservations\CostCalculator;

interface CostStrategy
{
    public function calculate($vehicle, $days);
}