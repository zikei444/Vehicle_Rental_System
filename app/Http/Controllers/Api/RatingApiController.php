<?php

// app/Http/Controllers/Api/RatingApiController.php
namespace App\Http\Controllers\Api;
use App\Models\Rating; 
use App\Http\Controllers\Controller;
use App\Services\RatingService;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class RatingApiController extends Controller {
    protected $ratingService;

    public function __construct(RatingService $ratingService) {
        $this->ratingService = $ratingService;
    }

    public function summary($vehicleId) {
        return $this->ratingService->getVehicleRatingSummary($vehicleId, 'approved');
    }

    // 获取审核通过的评论
    public function index($vehicleId) {
        return response()->json($this->ratingService->getApprovedRatings($vehicleId));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id'  => 'required|exists:vehicles,id',
            'rating'      => 'required|integer|min:1|max:5',
            'feedback'    => 'nullable|string|max:500',
        ]);

        $rating = Rating::create([
            'customer_id' => $request->customer_id,
            'vehicle_id'  => $request->vehicle_id,
            'rating'      => $request->rating,
            'feedback'    => $request->feedback,
            'status'      => 'pending', // 默认状态
        ]);

        return response()->json([
            'message' => 'Rating submitted successfully',
            'rating' => $rating
        ], 201);
    }



    public function rating($vehicleId) {
        $vehicle = Vehicle::findOrFail($vehicleId);
        return response()->json([
            'vehicle_id' => $vehicleId,
            'average_rating' => $vehicle->average_rating,
        ]);
    }
}


