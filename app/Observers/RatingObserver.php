<?php
// app/Observers/RatingObserver.php
namespace App\Observers;

use App\Models\Rating;

class RatingObserver {
    public function created(Rating $rating) {
        if ($rating->status === 'approved') {
            $this->updateVehicleAverage($rating);
        }
    }

    public function updated(Rating $rating) {
        if ($rating->status === 'approved') {
            $this->updateVehicleAverage($rating);
        }
    }

    public function deleted(Rating $rating) {
        $this->updateVehicleAverage($rating);
    }

    private function updateVehicleAverage(Rating $rating) {
        $vehicle = $rating->vehicle;
        if ($vehicle) {
            $avg = $vehicle->ratings()->approved()->avg('rating');
            $vehicle->update(['average_rating' => $avg ?? 0]);
            $vehicle->saveQuietly(); // ✅ 避免 Observer 死循环

        }
    }
}
