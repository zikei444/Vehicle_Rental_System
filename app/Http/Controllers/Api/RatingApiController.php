<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RatingService;
use App\Models\Vehicle;
use App\Models\Rating;
use Illuminate\Http\Request;


class RatingApiController extends Controller
{
    protected $ratingService;

    public function __construct(RatingService $ratingService) {
        $this->ratingService = $ratingService;
    }

    public function index($vehicleId) {
        return response()->json($this->ratingService->getRatings($vehicleId));
    }

    public function store(Request $request, $vehicleId) {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'rating'      => 'required|integer|min:1|max:5',
            'feedback'    => 'nullable|string|max:500',
        ]);

        $rating = $this->ratingService->addRating(
            $vehicleId,
            $request->customer_id,
            $request->rating,
            $request->feedback
        );

        return response()->json($rating, 201);
    }

    public function average($vehicleId) {
        $vehicle = Vehicle::findOrFail($vehicleId);
        return response()->json($this->ratingService->getAverageRating($vehicle));
    }

    // Check if customer has rated a vehicle ADDED BY ZK
    public function hasRated($vehicleId, $customerId)
    {
        $hasRated = Rating::where('customer_id', $customerId)
                          ->where('vehicle_id', $vehicleId)
                          ->exists();

        return response()->json([
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'has_rated' => $hasRated
        ]);
    }
}
