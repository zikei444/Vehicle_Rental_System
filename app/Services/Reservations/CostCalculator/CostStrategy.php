<?php

// STUDENT NAME: LIEW ZI KEI 
// STUDENT ID: 23WMR14570

namespace App\Services\Reservations\CostCalculator;

interface CostStrategy
{
    public function calculate($vehicle, $days);
}