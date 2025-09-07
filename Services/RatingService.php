<?php

namespace App\Services;

use App\Models\Rating;

class RatingService {
    public function getRating($vehicle) {
        return [
            'vehicle_id' => $vehicle->id,
            'average_rating' => $vehicle->average_rating,
        ];
    }

    public function addRating($vehicleId,$customerId,$score, $feedback) {
        return Rating::create([
            'vehicle_id' => $vehicleId,
            'customer_id' => $customerId, // 示例，记得换成真实的
            'rating' => $score,
            'feedback' => $feedback,
            'status' => 'pending'
        ]);
    }
}
