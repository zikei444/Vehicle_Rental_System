<?php

namespace App\Services;

use App\Models\Rating;

class RatingService
{
    public function getRatings($vehicleId)
    {
        return Rating::where('vehicle_id', $vehicleId)->approved()->get();
    }

    // Check if a customer has rated a vehicle ADDED BY ZK
    public function hasRated(int $customerId, int $vehicleId): bool
    {
        return Rating::where('customer_id', $customerId)
            ->where('vehicle_id', $vehicleId)
            ->exists();
    }

    // Return JSON for hasRated JSON JSON JSON JSON ADDED BY ZK
    public function hasRatedJson(int $customerId, int $vehicleId)
    {
        return response()->json([
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'has_rated' => $this->hasRated($customerId, $vehicleId)
        ]);
    }

    public function getAverageRating($vehicle)
    {
        return [
            'vehicle_id' => $vehicle->id,
            'average_rating' => $vehicle->average_rating,
        ];
    }

    public function addRating($vehicleId, $customerId, $score, $feedback = null)
    {
        return Rating::create([
            'vehicle_id' => $vehicleId,
            'customer_id' => $customerId,
            'admin_id' => null,
            'rating' => $score,
            'feedback' => $feedback,
            'status' => 'pending'
        ]);
    }
}
