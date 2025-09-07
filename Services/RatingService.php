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

    public function addRating($vehicleId, $score) {
        return Rating::create([
            'vehicle_id' => $vehicleId,
            'score' => $score,
        ]);
    }
}
