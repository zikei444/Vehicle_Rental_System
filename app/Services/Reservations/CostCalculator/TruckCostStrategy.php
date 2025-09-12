<!-- 
STUDENT NAME: LIEW ZI KEI 
STUDENT ID: 23WMR14570
-->

<?php

namespace App\Services\Reservations\CostCalculator;

class TruckCostStrategy implements CostStrategy
{
    public function calculate($vehicle, $days): array
    {
        $base = $vehicle['rental_price'] * $days;
        $extra = $base * 0.1; // 10% extra fee
        $total = $base + $extra;

        return [
            'total' => $total,
            'breakdown' => [
                'base_rental' => $base,
                'extra_fee' => $extra,
            ],
            'message' => "Base: RM{$base}, Extra 10% fee: RM{$extra}"
        ];
    }
}