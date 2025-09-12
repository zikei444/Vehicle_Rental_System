<?php



namespace App\Services\Reservations\CostCalculator;

class VanCostStrategy implements CostStrategy
{
    public function calculate($vehicle, $days): array
    {
        $base = $vehicle['rental_price'] * $days;
        $insurance = 50; // flat RM50 insurance
        $total = $base + $insurance;

        return [
            'total' => $total,
            'breakdown' => [
                'base_rental' => $base,
                'insurance' => $insurance,
            ],
            'message' => "Base: RM{$base}, Insurance: RM{$insurance}"
        ];
    }
}