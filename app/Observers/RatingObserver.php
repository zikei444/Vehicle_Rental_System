<?php

// app/Observers/RatingObserver.php
namespace App\Observers;

use App\Models\Rating;

class RatingObserver {
    public function created(Rating $rating) {
        $this->updateVehicleAverage($rating);
    }

    public function updated(Rating $rating) {
        $this->updateVehicleAverage($rating);
    }

    public function deleted(Rating $rating) {
        $this->updateVehicleAverage($rating);
    }

    private function updateVehicleAverage(Rating $rating) {
        $vehicle = $rating->vehicle;
        $avg = $vehicle->ratings()->avg('score');
        $vehicle->update(['average_rating' => $avg ?? 0]);
    }
}
