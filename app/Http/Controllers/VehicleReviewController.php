<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\RatingService;
use App\Models\Vehicle;
use App\Models\Reservation;
use App\Models\Rating;
use App\Services\UserService;

class VehicleReviewController extends Controller
{
    protected $ratingService;
    private UserService $userService;
    private string $ratingApi = '/api/ratings';

    public function __construct(RatingService $ratingService, UserService $userService)
    {
        $this->ratingService = $ratingService;
        $this->userService = $userService;
    }
    /**
     * Helper: fetch rating JSON either via API or service
     */
    private function getRatingJson(int $vehicleId, bool $useApi): ?array
    {
        if ($useApi) {
            $response = Http::timeout(10)->get(url($this->ratingApi . "/vehicle/{$vehicleId}"));
            if ($response->failed())
                return null;
            return $response->json()['data'] ?? null;
        } else {
            // Internal service
            $ratings = $this->ratingService->getVehicleRatings($vehicleId);
            return $ratings ? $ratings->toArray() : null;
        }
    }
        private function getVehicleJson(int $vehicleId, bool $useApi = false): ?array
    {
        if ($useApi) {
            $response = Http::timeout(10)->get(url($this->vehicleApi . '/' . $vehicleId));
            if ($response->failed()) return null;
            return $response->json()['data'] ?? null;
        } else {
            $jsonResponse = $this->vehicleService->find($vehicleId);
            if ($jsonResponse instanceof \Illuminate\Http\JsonResponse) {
                return $jsonResponse->getData(true)['data'] ?? null;
            }
            return is_array($jsonResponse) ? $jsonResponse : null;
        }
    }
    /**
     * Show rating form to create
     */
    public function create(Request $request, $vehicleId, $reservationId)
    {
        $useApi = (bool) $request->query('use_api', false);

                // Fetch vehicle from DB
                $vehicle = $this->getVehicleJson($vehicleId, $useApi);    
                

                $reservation = Reservation::findOrFail($reservationId);

        return view('ratings.create', compact('vehicle', 'reservation', 'useApi'));
    }

    /**
     * Show all ratings for a vehicle
     */
    public function showRatings(Request $request, $vehicleId)
    {
        try {
            $useApi = (bool) $request->query('use_api', false);

            if ($useApi) {
                $response = Http::timeout(10)->get(url("/api/vehicles/{$vehicleId}/ratings"));
                if ($response->failed()) {
                    throw new \Exception("Failed to fetch ratings");
                }
                $ratings = $response->json();
            } else {
                // ✅ 用你 RatingService 里面的 getVehicleRatings()
                $ratings = $this->ratingService->getVehicleRatings($vehicleId);
            }

            return view('ratings.index', compact('ratings', 'useApi'));

        } catch (\Exception $e) {
            return view('ratings.index', [
                'ratings' => collect([]),
                'error' => $e->getMessage(),
                'useApi' => false
            ]);
        }
    }

 

    public function viewRating($reservationId)
    {
        // 获取预约信息及车辆
        $reservation = Reservation::with('vehicle')->findOrFail($reservationId);

        // 直接用 reservation_id 来取唯一的评分和回复
        $rating = Rating::where('reservation_id', $reservation->id)->first();

        // 如果没有自己的评分，则只显示已批准的评分
        if (!$rating) {
            $rating = Rating::where('vehicle_id', $reservation->vehicle_id)
                ->approved()
                ->first();
        }

        return view('ratings.viewRating', compact('reservation', 'rating'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'reservation_id' => 'required|exists:reservations,id',
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:500',
        ]);

        $customerId = auth()->user()->customer->id;

        if (
            Rating::where('customer_id', $customerId)
                ->where('reservation_id', $request->reservation_id)
                ->exists()
        ) {
            return response()->json(['error' => 'Already rated'], 403);
        }

        $rating = Rating::create([
            'customer_id' => $customerId,
            'vehicle_id' => $request->vehicle_id,
            'reservation_id' => $request->reservation_id,
            'rating' => $request->rating,
            'feedback' => $request->feedback,
            'status' => 'pending',
        ]);

        // Observer triggers notification to admin

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully!',
            'data' => $rating
        ]);
    }

    /**
     * Show average rating
     */
    // public function showAverage(Request $request, $vehicleId)
    // {
    //     try {
    //         $useApi = $request->query('use_api', false);

    //         if ($useApi) {
    //             $response = Http::timeout(10)->get(url($this->ratingApi . "/average/{$vehicleId}"));
    //             $average = $response->successful() ? $response->json() : ['average_rating' => 0];
    //         } else {
    //             $vehicle = Vehicle::findOrFail($vehicleId);
    //             $average = $this->ratingService->getAverageRating($vehicle);
    //         }

    //         return view('ratings.average', compact('average', 'useApi'));
    //     } catch (\Exception $e) {
    //         return view('ratings.average', [
    //             'average' => ['average_rating' => 0],
    //             'error' => $e->getMessage()
    //         ]);
    //     }
    // }
    public function showAverage($vehicleId)
    {
        try {
            $vehicle = \App\Models\Vehicle::findOrFail($vehicleId);

            return view('ratings.average', compact('vehicle'));
        } catch (\Exception $e) {
            return view('ratings.average', [
                'vehicle' => null,
                'error' => $e->getMessage()
            ]);
        }
    }

}
