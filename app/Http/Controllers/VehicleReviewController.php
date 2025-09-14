<?php
// STUDENT NAME: Kek Xin Ying
// STUDENT ID: 23WMR14547

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\RatingService;
use App\Models\Vehicle;
use App\Models\Reservation;
use App\Models\Rating;
use App\Services\UserService;
use App\Services\VehicleService;

class VehicleReviewController extends Controller
{
    protected $ratingService;
    private UserService $userService;
    private UserService $vehicleService;
    private string $ratingApi = '/api/ratings';
    private string $vehicleApi = '/api/vehicles';
    private string $reservationApi = '/api/reservations';

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

                // Fetch vehicle & reservation using api
                $vehicle = $this->getVehicleJson($vehicleId, $useApi);    
                
               $reservation = $this->getReservationJson($reservationId, $useApi);
                //$reservation = Reservation::findOrFail($reservationId);

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
                // ✅ use RatingService inside getVehicleRatings()
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
        // take reservation and vehicle
        $reservation = Reservation::with('vehicle')->findOrFail($reservationId);

        // use reservation_id get rating
        $rating = Rating::where('reservation_id', $reservation->id)->first();

        // if no own rating, show only approved rating 
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


    public function showAverage($vehicleId)
    {
        try {
                        // Use API to fetch vehicle info
            $vehicle = $this->getVehicleJson($vehicleId, true);
           // $vehicle = Vehicle::findOrFail($vehicleId);

            return view('ratings.average', compact('vehicle'));
        } catch (\Exception $e) {
            return view('ratings.average', [
                'vehicle' => null,
                'error' => $e->getMessage()
            ]);
        }
    }
    /**
     * Fetch reservation JSON via API
     */
    private function getReservationJson(int $reservationId, bool $useApi = true): ?array
    {
        if ($useApi) {
            $response = Http::timeout(10)->get(url($this->reservationApi . '/' . $reservationId));
            if ($response->failed()) return null;
            return $response->json()['data'] ?? $response->json(); // support both formats
        }
        return null; // no DB fallback for reservation
    }
    /**
     * Create a helper to hasRatedApi Fetch reservation JSON via API
     */

}
