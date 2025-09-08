<?php

// app/Services/RatingService.php
namespace App\Services;

use App\Models\Rating;

class RatingService {
    public function getApprovedRatings($vehicleId) {
        return Rating::approved()->where('vehicle_id', $vehicleId)->get();
    }

    public function addRating($customerId, $vehicleId, $rating, $feedback = null) {
        return Rating::create([
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'rating' => $rating,
            'feedback' => $feedback,
            'status' => 'pending', // 默认待审核
        ]);
    }
    public function hasRated($customerId, $vehicleId) {
        return Rating::where('customer_id', $customerId)
                     ->where('vehicle_id', $vehicleId)
                     ->exists();
    }
}



// namespace App\Services;

// use App\Models\Rating;

// class RatingService
// {
//     public function getRatings($vehicleId)
//     {
//         return Rating::where('vehicle_id', $vehicleId)->approved()->get();
//     }

//     // 检查用户是否对该车打过分
// public function hasRated(int $customerId, int $vehicleId): bool
//      {
//          return Rating::where('customer_id', $customerId)
//              ->where('vehicle_id', $vehicleId)
//              ->exists();
//      }

//     // 返回 JSON（方便直接给 API 用）
//     public function hasRatedJson(int $customerId, int $vehicleId)
//     {
//         return response()->json([
//             'customer_id' => $customerId,
//             'vehicle_id'  => $vehicleId,
//             'has_rated'   => $this->hasRated($customerId, $vehicleId)
//         ]);
//     }

//     // 获取车辆平均分
//     public function getAverageRating($vehicle)
//     {
//         return [
//             'vehicle_id'     => $vehicle->id,
//             'average_rating' => $vehicle->average_rating,
//         ];
//     }

//     // 添加评分
//     public function addRating($vehicleId, $customerId, $score, $feedback = null)
//     {
//         return Rating::create([
//             'vehicle_id'  => $vehicleId,
//             'customer_id' => $customerId,
//             'admin_id'    => null,
//             'rating'      => $score,
//             'feedback'    => $feedback,
//             'status'      => 'pending'
//         ]);
//     }
// }
