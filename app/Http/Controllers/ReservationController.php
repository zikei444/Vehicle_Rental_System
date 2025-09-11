<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Reservation;
use App\Services\VehicleService;
use App\Services\RatingService;
use App\Services\UserService;
use App\Services\Reservations\CostCalculator\CarCostStrategy;
use App\Services\Reservations\CostCalculator\TruckCostStrategy;
use App\Services\Reservations\CostCalculator\VanCostStrategy;

class ReservationController extends Controller
{
    // Vehicle Service
    private VehicleService $vehicleService;

    // Rating Service
    private RatingService $ratingService;

    // User Service
    private UserService $userService;

    // API front URL
    private string $vehicleApi = '/api/vehicles';
    private string $ratingApi = '/api/ratings';
    private string $userApi = '/api/user';

    // Construct for Services
    public function __construct(VehicleService $vehicleService, RatingService $ratingService, UserService $userService)
    {
        $this->vehicleService = $vehicleService;
        $this->ratingService = $ratingService;
        $this->userService = $userService;
    }

    // Helper: fetch vehicle JSON (if use api then http else go through service -- internal)
    private function getVehicleJson(int $vehicleId, bool $useApi): ?array
    {
        if ($useApi) {
            // External API HTTP 
            $response = Http::timeout(10)->get(url($this->vehicleApi . '/' . $vehicleId));
            if ($response->failed())
                return null;
            return $response->json()['data'] ?? null;
        } else {
            // Internal service returns JsonResponse, so unwrap it
            $jsonResponse = $this->vehicleService->find($vehicleId);
            if ($jsonResponse instanceof \Illuminate\Http\JsonResponse) {
                return $jsonResponse->getData(true)['data'] ?? null;
            }
            // If already an array, just return
            return is_array($jsonResponse) ? $jsonResponse : null;
        }
    }

    // Show process page
    public function process(Request $request)
    {
        $vehicleId = (int) $request->query('vehicle_id');

        // Check if external service required
        $useApi = (bool) $request->query('use_api', false);

        // Can helper to get response
        $vehicle = $this->getVehicleJson($vehicleId, $useApi);

        if (!$vehicle) {
            return back()->with('error', 'Vehicle not found');
        }

        // Process page
        return view('reservations.reservationProcess', compact('vehicle'));
    }

    // Calculate cost via AJAX (Js + HTTP)
    public function calculateAjax(Request $request)
    {
        $vehicleId = (int) $request->vehicle_id;
        $useApi = (bool) $request->query('use_api', false);

        $vehicle = $this->getVehicleJson($vehicleId, $useApi);
        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found']);
        }

        $days = max(1, (int) $request->days);
        $pickup = new \DateTime($request->pickup_date);
        $return = (clone $pickup)->modify("+{$days} days");

        $allowedTypes = ['car', 'van', 'truck'];
        $type = strtolower($vehicle['type']);

        if (!in_array($type, $allowedTypes)) {
            return response()->json(['error' => 'Invalid vehicle type'], 400);
        }

        // Strategy use based on vehicle type and call respective strategy class
        $strategyClass = "App\\Services\\Reservations\\CostCalculator\\" . ucfirst($type) . "CostStrategy";
        $strategy = new $strategyClass();
        $costDetails = $strategy->calculate($vehicle, $days);

        // Return to F-E
        return response()->json([
            'vehicle_id' => $vehicleId,
            'totalCost' => $costDetails['total'],
            'breakdown' => $costDetails['breakdown'],
            'message' => $costDetails['message'],
            'days' => $days,
            'pickup' => $pickup->format('Y-m-d'),
            'return' => $return->format('Y-m-d'),
        ]);
    }

    // Confifm reservation
    public function confirm(Request $request)
    {
        // Check login first
        if (!auth()->check()) {
            return view('reservations.reservationProcess', [
                'vehicle' => $this->getVehicleJson((int) $request->vehicle_id, (bool) $request->query('use_api', false)),
                'error_popup' => 'You must be logged in to proceed to rent car.',
            ]);
        }

        $useApi = (bool) $request->query('use_api', false);
        $customerId = null;

        if ($useApi) {
            $response = Http::timeout(5)->get(url($this->userApi . '/customer-id'), [
                'user_id' => auth()->id()
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch customer id via API'], 500);
            }

            $customerId = $response->json('customer_id');
        } else {
            $response = $this->userService->getCustomerIdByUserId(auth()->id());

            // Decode json
            $data = $response->getData(true); // to array
            $customerId = $data['customer_id'] ?? null;

            if (!$customerId) {
                return response()->json(['error' => 'Customer not found'], 404);
            }
        }

        if (!$customerId) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        // Check if customer already has an ongoing reservation
        $hasOngoing = Reservation::where('customer_id', $customerId)
            ->where('status', 'ongoing')
            ->exists();

        if ($hasOngoing) {
            // Tell them already got ongoing
            return view('reservations.reservationProcess', [
                'vehicle' => $this->getVehicleJson((int) $request->vehicle_id, (bool) $request->query('use_api', false)),
                'error_popup' => 'You already have an ongoing reservation. Please complete it before booking another vehicle.',
            ]);
        }

        $vehicle = $this->getVehicleJson((int) $request->vehicle_id, (bool) $request->query('use_api', false));
        if (!$vehicle)
            return back()->with('error', 'Vehicle not found');

        return view('reservations.reservationPayment', [
            'vehicle' => $vehicle,
            'pickup_date' => $request->pickup_date,
            'return_date' => $request->return_date,
            'days' => $request->days,
            'total_cost' => $request->total_cost,
        ]);
    }

    // Payment Processing and Create Reservation
    public function paymentProcess(Request $request)
    {
        // Common validation rules
        $rules = [
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'pickup_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:pickup_date',
            'days' => 'required|integer|min:1',
            'total_cost' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,bank_transfer',
        ];

        // Conditional validation for card payments
        if ($request->payment_method === 'card') {
            $rules = array_merge($rules, [
                'card_name' => 'required|string|max:255',
                'card_number' => 'required|digits_between:13,19',
                'cvv' => 'required|digits:3',
                'expiry_month' => 'required|integer|between:1,12',
                'expiry_year' => 'required|integer|min:' . date('Y'),
            ]);
        }

        // Validate input
        $validated = $request->validate($rules);

        $useApi = (bool) $request->query('use_api', false);
        $customerId = null;

        if ($useApi) {
            $response = Http::timeout(5)->get(url($this->userApi . '/customer-id'), [
                'user_id' => auth()->id()
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch customer id via API'], 500);
            }

            $customerId = $response->json('customer_id');
        } else {
            $response = $this->userService->getCustomerIdByUserId(auth()->id());

            // decode json
            $data = $response->getData(true); // to array
            $customerId = $data['customer_id'] ?? null;

            if (!$customerId) {
                return response()->json(['error' => 'Customer not found'], 404);
            }
        }

        if (!$customerId) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        // Create reservation
        $reservation = Reservation::create(array_merge($validated, ['customer_id' => $customerId]));
        $useApi = (bool) $request->query('use_api', false);
        // Helper Method
        $vehicleData = $this->getVehicleJson($validated['vehicle_id'], $useApi);
        $vehicle = is_array($vehicleData) ? (object) $vehicleData : $vehicleData;

        // Safety fallback if vehicle data is no no
        if (!$vehicle) {
            $vehicle = (object) [
                'id' => $validated['vehicle_id'],
                'registration_number' => 'Unknown',
                'brand' => 'Unknown',
                'model' => '',
                'type' => 'Unknown',
                'availability_status' => 'Unknown',
                'rental_price' => 0,
            ];
        }

        // Update vehicle status- to rented
        if ($useApi) {
            // External API 
            Http::post(url($this->vehicleApi . '/update-status'), [
                'vehicle_id' => $validated['vehicle_id'],
                'status' => 'rented',
            ]);
        } else {
            // Internal Service
            $this->vehicleService->updateStatus($validated['vehicle_id'], 'rented');
        }

        return view('reservations.reservationSuccess', [
            'reservation_id' => $reservation->id,
            'vehicle' => $vehicle,
            'pickup_date' => $validated['pickup_date'],
            'return_date' => $validated['return_date'],
            'days' => $validated['days'],
            'total_cost' => $validated['total_cost'],
            'payment_method' => ucfirst(str_replace('_', ' ', $validated['payment_method'])),
        ]);
    }

    // Show current on going resevation
    public function myReservations(Request $request)
    {
        $useApi = (bool) $request->query('use_api', false);
        $customerId = null;

        if ($useApi) {
            $response = Http::timeout(5)->get(url($this->userApi . '/customer-id'), [
                'user_id' => auth()->id()
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch customer id via API'], 500);
            }

            $customerId = $response->json('customer_id');
        } else {
            $response = $this->userService->getCustomerIdByUserId(auth()->id());

            // decode json
            $data = $response->getData(true); // to array
            $customerId = $data['customer_id'] ?? null;

            if (!$customerId) {
                return response()->json(['error' => 'Customer not found'], 404);
            }
        }

        if (!$customerId) {
            return response()->json(['error' => 'Customer not found'], 404);
        }


        // Fetch the first ongoing reservation
        $reservation = Reservation::where('customer_id', $customerId)
            ->where('status', 'ongoing')
            ->orderBy('pickup_date', 'asc')
            ->first();

        $noOngoing = false;

        if ($reservation) {
            // Ensure vehicle_id exists
            if ($reservation->vehicle_id) {

                $vehicleData = $this->getVehicleJson($reservation->vehicle_id, $useApi);

                // Convert JsonResponse to array if needed
                if ($vehicleData instanceof \Illuminate\Http\JsonResponse) {
                    $vehicleData = $vehicleData->getData(true)['data'] ?? null;
                }

                // Convert array to object -- to display in blade view
                $reservation->vehicle = is_array($vehicleData) ? (object) $vehicleData : $vehicleData;

                // Safety fallback
                if (!$reservation->vehicle) {
                    $reservation->vehicle = (object) [
                        'brand' => 'Unknown',
                        'model' => '',
                        'rental_price' => 0,
                        'type' => 'Unknown'
                    ];
                }

            } else {
                // No vehicle_id in reservation
                $reservation->vehicle = (object) [
                    'brand' => 'Unknown',
                    'model' => '',
                    'rental_price' => 0,
                    'type' => 'Unknown'
                ];
            }
        } else {
            $noOngoing = true; // No ongoing reservation
            $reservation = null;
        }

        return view('reservations.myReservation', compact('reservation', 'noOngoing'));
    }

    // Let mark reservation as completed 
    public function complete(Request $request, $id)
    {
        
        $useApi = (bool) $request->query('use_api', false);
        $customerId = null;

        if ($useApi) {
            $response = Http::timeout(5)->get(url($this->userApi . '/customer-id'), [
                'user_id' => auth()->id()
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch customer id via API'], 500);
            }

            $customerId = $response->json('customer_id');
        } else {
            $response = $this->userService->getCustomerIdByUserId(auth()->id());

            // decode json
            $data = $response->getData(true); // to array
            $customerId = $data['customer_id'] ?? null;

            if (!$customerId) {
                return response()->json(['error' => 'Customer not found'], 404);
            }
        }

        if (!$customerId) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $reservation = Reservation::findOrFail($id);

        // Check ownership: prevent IDOR
        if ($reservation->customer_id !== $customerId) {
            abort(403, 'Unauthorized action.');
        }

        
        $reservation->status = 'completed';
        $reservation->save();

        // Update vehicle back to available
        if ($useApi) {
            Http::post(url($this->vehicleApi . '/update-status'), [
                'vehicle_id' => $reservation->vehicle_id,
                'status' => 'available',
            ]);
        } else {
            $this->vehicleService->updateStatus($reservation->vehicle_id, 'available');
        }

        return redirect()->route('reservations.history')->with('success', 'Reservation marked as completed.');
    }

    // Let Customer cancel reservation
    public function cancel(Request $request, $id)
    {
        $useApi = (bool) $request->query('use_api', false);
        $customerId = null;

        if ($useApi) {
            $response = Http::timeout(5)->get(url($this->userApi . '/customer-id'), [
                'user_id' => auth()->id()
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch customer id via API'], 500);
            }

            $customerId = $response->json('customer_id');
        } else {
            $response = $this->userService->getCustomerIdByUserId(auth()->id());

            // decode json
            $data = $response->getData(true); // to array
            $customerId = $data['customer_id'] ?? null;

            if (!$customerId) {
                return response()->json(['error' => 'Customer not found'], 404);
            }
        }

        if (!$customerId) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $reservation = Reservation::findOrFail($id);

        // Check ownership: prevent IDOR
        if ($reservation->customer_id !== $customerId) {
            abort(403, 'Unauthorized action.');
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        // Update vehicle back to available
        if ($useApi) {
            Http::post(url($this->vehicleApi . '/update-status'), [
                'vehicle_id' => $reservation->vehicle_id,
                'status' => 'available',
            ]);
        } else {
            $this->vehicleService->updateStatus($reservation->vehicle_id, 'available');
        }

        return redirect()->route('reservations.history')->with('success', 'Reservation cancelled.');
    }

    // History
    public function reservationHistory(Request $request)
    {
        $useApi = (bool) $request->query('use_api', false);
        $customerId = null;

        if ($useApi) {
            $response = Http::timeout(5)->get(url($this->userApi . '/customer-id'), [
                'user_id' => auth()->id()
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch customer id via API'], 500);
            }

            $customerId = $response->json('customer_id');
        } else {
            $response = $this->userService->getCustomerIdByUserId(auth()->id());

            // decode json
            $data = $response->getData(true); // to array
            $customerId = $data['customer_id'] ?? null;

            if (!$customerId) {
                return response()->json(['error' => 'Customer not found'], 404);
            }
        }

        if (!$customerId) {
            return response()->json(['error' => 'Customer not found'], 404);
        }



        $reservations = Reservation::where('customer_id', $customerId)
            ->orderBy('pickup_date', 'desc')
            ->get();

        foreach ($reservations as $res) {
            // Vehicle
            $vehicleData = $this->getVehicleJson($res->vehicle_id, $useApi);

            // Convert to object for Blade
            if ($vehicleData instanceof \Illuminate\Http\JsonResponse) {
                $vehicleData = $vehicleData->getData(true)['data'] ?? null;
            }
            $res->vehicle = is_array($vehicleData) ? (object) $vehicleData : $vehicleData;

            // Safety fallback
            if (!$res->vehicle) {
                $res->vehicle = (object) [
                    'brand' => 'Unknown',
                    'model' => '',
                    'registration_number' => 'Unknown',
                    'type' => 'Unknown',
                    'rental_price' => 0
                ];
            }

            // Rating
            if ($useApi) {
                $ratingResponse = Http::get(url($this->ratingApi . "/has-rated/{$res->vehicle_id}/{$customerId}"))->json();
                $res->hasRated = $ratingResponse['has_rated'] ?? false;
            } else {
                $res->hasRated = $this->ratingService->hasRated($customerId, $res->vehicle_id);
            }
        }

        return view('reservations.reservationHistory', compact('reservations', 'useApi'));
    }
}