<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\RatingService;
use App\Models\Vehicle;
use App\Models\Reservation;
use App\Models\Rating;

class VehicleReviewController extends Controller
{
    protected $ratingService;

    public function __construct(RatingService $ratingService) {
        $this->ratingService = $ratingService;
    }
    public function create($vehicleId)
    {
        $vehicle = \App\Models\Vehicle::findOrFail($vehicleId);
        return view('ratings.create', compact('vehicle'));
    }
    public function showRatings(Request $request, $vehicleId) {
        try {
            $useApi = $request->query('use_api', false);

            if ($useApi) {
                $response = Http::timeout(10)->get(url("/api/vehicles/{$vehicleId}/ratings"));
                if ($response->failed()) throw new \Exception("Failed to fetch ratings");
                $ratings = $response->json();
            } else {
                $ratings = $this->ratingService->getApprovedRatings($vehicleId);
            }

            return view('ratings.index', compact('ratings'));
        } catch (\Exception $e) {
            return view('ratings.index', [
                'ratings' => collect([]),
                'error' => $e->getMessage()
            ]);
        }
    }
        public function viewRating($reservationId)
        {
            // 先获取预约信息及车辆
            $reservation = Reservation::with('vehicle')->findOrFail($reservationId);

            // 查找该用户对该车辆的评分
            $rating = Rating::where('vehicle_id', $reservation->vehicle_id)
                            ->where('customer_id', $reservation->customer_id)
                            ->approved()
                            ->first();

            return view('ratings.viewRating', compact('reservation', 'rating'));
        }


            public function store(Request $request)
            {
                $request->validate([
                    'vehicle_id' => 'required|exists:vehicles,id',
                    'rating' => 'required|integer|min:1|max:5',
                    'feedback' => 'nullable|string',
                ]);

                $vehicleId = $request->vehicle_id;
                $customerId = auth()->id();

                // 检查该用户是否已经对该车辆评价过
                $existing = Rating::where('vehicle_id', $vehicleId)
                                ->where('customer_id', $customerId)
                                ->first();

                if ($existing) {
                    return response()->json([
                        'error' => 'You have already rated this vehicle.'
                    ], 403); // Forbidden
                }

                $rating = Rating::create([
                    'vehicle_id' => $vehicleId,
                    'customer_id' => $customerId,
                    'rating' => $request->rating,
                    'feedback' => $request->feedback,
                    'status' => 'pending', // 或 approved，视你业务逻辑
                ]);

                return response()->json(['rating' => $rating], 200);
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
