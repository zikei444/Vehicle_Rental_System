<?php

namespace App\Services;
use Illuminate\Http\JsonResponse;

use App\Models\Rating;

class RatingService
{
    // 添加评分
    public function addRating(int $customerId, int $vehicleId, int $reservationId, int $rating, ?string $feedback = null): Rating
    {
        return Rating::create([
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'reservation_id' => $reservationId,
            'rating' => $rating,
            'feedback' => $feedback,
            'status' => 'pending', // 默认待审核
        ]);
    }

    // 检查用户是否已评分
    public function hasRated(int $customerId, int $reservationId): bool
    {
        return Rating::where('customer_id', $customerId)
            ->where('reservation_id', $reservationId)
            ->exists();
    }

    // Only for approved
    public function getVehicleRatingSummary(int $vehicleId, string $status = 'approved')
{
    $query = Rating::where('vehicle_id', $vehicleId)
                   ->where('status', $status);

    $average = $query->avg('rating');
    $count   = $query->count();

    return response()->json([
        'data' => [
            'average' => $average !== null ? (float) $average : 0,
            'count'   => $count
        ]
    ]);
}

    // 获取某车辆的平均评分
    public function getAverageRating(int $vehicleId): ?float
    {
        return Rating::where('vehicle_id', $vehicleId)
            ->approved()
            ->avg('rating');
    }

    // 获取用户对某车的评分（只返回 approved）
    public function getUserRatingForVehicle(int $customerId, int $vehicleId): ?Rating
    {
        return Rating::where('customer_id', $customerId)
            ->where('vehicle_id', $vehicleId)
            ->approved()
            ->first();
    }

    // 获取某车辆的所有评分（approved）
    public function getVehicleRatings(int $vehicleId)
    {
        return Rating::where('vehicle_id', $vehicleId)
        
            ->with(['customer', 'vehicle'])
            ->get();
    }



}
