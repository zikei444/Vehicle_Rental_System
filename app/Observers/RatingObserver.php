<?php

namespace App\Observers;

use App\Models\Rating;
use Illuminate\Support\Facades\Log;

class RatingObserver
{
    public function created(Rating $rating): void {
        $this->updateVehicleAverage($rating);

        if (!empty($rating->feedback)) {
            Log::info("新评分通知: 车辆ID {$rating->vehicle_id}, 用户ID {$rating->customer_id}, 分数 {$rating->rating}, 评论: {$rating->feedback}");
        }
    }

    public function updated(Rating $rating): void {
        $this->updateVehicleAverage($rating);
    }

    public function deleted(Rating $rating): void {
        $this->updateVehicleAverage($rating);
    }

    private function updateVehicleAverage(Rating $rating): void {
        $vehicle = $rating->vehicle;
        if ($vehicle) {
            $avg = $vehicle->ratings()->approved()->avg('rating');
            $vehicle->update(['average_rating' => $avg ?? 0]);
        }
    }
}
