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
    private string $ratingApi = '/api/ratings';

    public function __construct(RatingService $ratingService) {
        $this->ratingService = $ratingService;
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
    /**
     * Show rating form to create
     */
    public function create($vehicleId, $reservationId)
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        $reservation = Reservation::findOrFail($reservationId);

        return view('ratings.create', compact('vehicle', 'reservation'));
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
                    'error'   => $e->getMessage(),
                    'useApi'  => false
                ]);
            }
        }

        /**
         * View rating for a reservation
         */
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

            /**
             * Store new rating
             */
            public function store(Request $request)
            {dd($request->all());

                $request->validate([
                    'vehicle_id' => 'required|exists:vehicles,id',
                    'reservation_id' => 'required|exists:reservations,id', 
                    'rating' => 'required|integer|min:1|max:5',
                    'feedback' => 'nullable|string',
                ]);
                $reservationId = $request->reservation_id;
                $vehicleId = $request->vehicle_id;
                $customerId = auth()->id();


            // 检查是否已经评价过
            //if ($this->ratingService->hasRated($customerId, $reservationId)) {
               if (Rating::where('customer_id', $customerId)
              ->where('reservation_id', $request->reservation_id)
              ->exists()) {
                   return response()->json(['error' => 'You have already rated this vehicle.'], 403);
            }

                $rating = $this->ratingService->addRating(
                    $customerId,
                    $vehicleId,
                    $reservationId,
                    $request->rating,
                    $request->feedback
                );

                return response()->json(['rating' => $rating], 200);
            }
    /**
     * Show average rating
     */
    public function showAverage(Request $request, $vehicleId) {
        try {
            $useApi = $request->query('use_api', false);

            if ($useApi) {
                $response = Http::timeout(10)->get(url($this->ratingApi . "/average/{$vehicleId}"));
                $average = $response->successful() ? $response->json() : ['average_rating' => 0];
            } else {
                $vehicle = Vehicle::findOrFail($vehicleId);
                $average = $this->ratingService->getAverageRating($vehicle);
            }

            return view('ratings.average', compact('average', 'useApi'));
        } catch (\Exception $e) {
            return view('ratings.average', [
                'average' => ['average_rating' => 0],
                'error' => $e->getMessage()
            ]);
        }
    }
}
