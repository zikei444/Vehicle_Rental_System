<?php

// app/Http/Controllers/VehicleReviewController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\CommentService;
use App\Services\RatingService;
use App\Models\Vehicle;

class VehicleReviewController extends Controller {
    protected $commentService;
    protected $ratingService;

    public function __construct(CommentService $commentService, RatingService $ratingService) {
        $this->commentService = $commentService;
        $this->ratingService = $ratingService;
    }

    public function showComments(Request $request, $vehicleId) {
        try {
            $useApi = $request->query('use_api', false);

            if ($useApi) {
                // 外部 API 调用
                $response = Http::timeout(10)->get(url("/api/vehicles/{$vehicleId}/comments"));
                if ($response->failed()) throw new \Exception("Failed to fetch comments");
                $comments = $response->json();
            } else {
                // 内部 Service 调用
                $comments = $this->commentService->getComments($vehicleId);
            }

            return view('vehicles.comments', compact('comments'));
        } catch (\Exception $e) {
            return view('vehicles.comments', [
                'comments' => collect([]),
                'error' => $e->getMessage()
            ]);
        }
    }

    public function showRating(Request $request, $vehicleId) {
        try {
            $useApi = $request->query('use_api', false);

            if ($useApi) {
                $response = Http::timeout(10)->get(url("/api/vehicles/{$vehicleId}/ratings"));
                if ($response->failed()) throw new \Exception("Failed to fetch rating");
                $rating = $response->json();
            } else {
                $vehicle = Vehicle::findOrFail($vehicleId);
                $rating = $this->ratingService->getRating($vehicle);
            }

            return view('vehicles.rating', compact('rating'));
        } catch (\Exception $e) {
            return view('vehicles.rating', [
                'rating' => ['average_rating' => 0],
                'error' => $e->getMessage()
            ]);
        }
    }
}

