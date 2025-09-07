<?php

namespace App\Services;

use App\Models\Rating;

class RatingService
{
    public function getRatings($vehicleId) {
        return Rating::where('vehicle_id', $vehicleId)->approved()->get();
}

    public function getAverageRating($vehicle) {
        return [
            'vehicle_id' => $vehicle->id,
            'average_rating' => $vehicle->average_rating,
        ];
    }

    public function addRating($vehicleId, $customerId, $score, $feedback = null) {
        return Rating::create([
            'vehicle_id'  => $vehicleId,
            'customer_id' => $customerId,
            'admin_id'    => null,
            'rating'      => $score,
            'feedback'    => $feedback,
            'status'      => 'pending'
        ]);
    }
}
