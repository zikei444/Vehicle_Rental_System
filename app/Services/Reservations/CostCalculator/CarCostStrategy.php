<?php

namespace App\Services\Reservations\CostCalculator;

class CarCostStrategy implements CostStrategy
{
    public function calculate($vehicle, $days): array
    {
        $base = $vehicle['rental_price'] * $days;

        return [
            'total' => $base,
            'breakdown' => [
                'base_rental' => $base,
            ],
            'message' => "Base rental for {$days} day(s): RM{$base}"
        ];
    }
}