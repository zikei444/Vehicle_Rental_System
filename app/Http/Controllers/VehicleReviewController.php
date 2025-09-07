<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\RatingService;
use App\Models\Vehicle;

class VehicleReviewController extends Controller
{
    protected $ratingService;

    public function __construct(RatingService $ratingService) {
        $this->ratingService = $ratingService;
    }

    public function showRatings(Request $request, $vehicleId) {
        try {
            $useApi = $request->query('use_api', false);

            if ($useApi) {
                $response = Http::timeout(10)->get(url("/api/vehicles/{$vehicleId}/ratings"));
                if ($response->failed()) throw new \Exception("Failed to fetch ratings");
                $ratings = $response->json();
            } else {
                $ratings = $this->ratingService->getRatings($vehicleId);
            }

            return view('ratings.index', compact('ratings'));
        } catch (\Exception $e) {
            return view('ratings.index', [
                'ratings' => collect([]),
                'error' => $e->getMessage()
            ]);
        }
    }

    public function showAverage(Request $request, $vehicleId) {
        try {
            $useApi = $request->query('use_api', false);

            if ($useApi) {
                $response = Http::timeout(10)->get(url("/api/vehicles/{$vehicleId}/ratings/average"));
                if ($response->failed()) throw new \Exception("Failed to fetch average rating");
                $average = $response->json();
            } else {
                $vehicle = Vehicle::findOrFail($vehicleId);
                $average = $this->ratingService->getAverageRating($vehicle);
            }

            return view('ratings.average', compact('average'));
        } catch (\Exception $e) {
            return view('ratings.average', [
                'average' => ['average_rating' => 0],
                'error' => $e->getMessage()
            ]);
        }
    }
}
