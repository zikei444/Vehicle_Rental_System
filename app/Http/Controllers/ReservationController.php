<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Reservation;
use App\Services\VehicleService;
use App\Services\Reservations\CostCalculator\CarCostStrategy;
use App\Services\Reservations\CostCalculator\TruckCostStrategy;
use App\Services\Reservations\CostCalculator\VanCostStrategy;

class ReservationController extends Controller
{
    private VehicleService $vehicleService;
    private string $vehicleApi = '/api/vehicles';

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    /**
     * Helper: fetch vehicle JSON either via API or service
     */
    private function getVehicleJson(int $vehicleId, bool $useApi): ?array
    {
        if ($useApi) {
            $response = Http::timeout(10)->get(url($this->vehicleApi . '/' . $vehicleId));
            if ($response->failed()) return null;
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

    /**
     * Show reservation process page
     */
    public function process(Request $request)
    {
        $vehicleId = (int)$request->query('vehicle_id');
        $useApi = (bool)$request->query('use_api', false);

        $vehicle = $this->getVehicleJson($vehicleId, $useApi);

        if (!$vehicle) {
            return back()->with('error', 'Vehicle not found');
        }

        return view('reservations.reservationProcess', compact('vehicle'));
    }

    /**
     * Calculate reservation cost via Ajax
     */
    public function calculateAjax(Request $request)
    {
        $vehicleId = (int)$request->vehicle_id;
        $useApi = (bool)$request->query('use_api', false);

        $vehicle = $this->getVehicleJson($vehicleId, $useApi);
        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found']);
        }

        $days = max(1, (int)$request->days);
        $pickup = new \DateTime($request->pickup_date);
        $return = (clone $pickup)->modify("+{$days} days");

        $strategyClass = "App\\Services\\Reservations\\CostCalculator\\" . ucfirst($vehicle['type']) . "CostStrategy";
        $strategy = new $strategyClass();
        $totalCost = $strategy->calculate($vehicle, $days);

        return response()->json([
            'vehicle_id' => $vehicleId,
            'totalCost'  => $totalCost,
            'days'       => $days,
            'pickup'     => $pickup->format('Y-m-d'),
            'return'     => $return->format('Y-m-d'),
        ]);
    }

    // Confifm reservation
    public function confirm(Request $request)
    {
        $customerId = 1; // TODO: replace with Auth::id()

        // Check if customer already has an ongoing reservation
        $hasOngoing = Reservation::where('customer_id', $customerId)
            ->where('status', 'ongoing')
            ->exists();

        if ($hasOngoing) {
            // Instead of back(), pass a flag to the view to show a dialog
            return view('reservations.reservationProcess', [
                'vehicle' => $this->getVehicleJson((int)$request->vehicle_id, (bool)$request->query('use_api', false)),
                'error_popup' => 'You already have an ongoing reservation. Please complete it before booking another vehicle.',
            ]);
        }

        $vehicle = $this->getVehicleJson((int)$request->vehicle_id, (bool)$request->query('use_api', false));
        if (!$vehicle) return back()->with('error', 'Vehicle not found');

        return view('reservations.reservationPayment', [
            'vehicle' => $vehicle,
            'pickup_date' => $request->pickup_date,
            'return_date' => $request->return_date,
            'days' => $request->days,
            'total_cost' => $request->total_cost,
        ]);
    }

/**
 * Payment processing + reservation creation
 */
public function paymentProcess(Request $request)
{
    // 1️⃣ Validate input
    $validated = $request->validate([
        'vehicle_id'     => 'required|integer',
        'pickup_date'    => 'required|date',
        'return_date'    => 'required|date',
        'days'           => 'required|integer|min:1',
        'total_cost'     => 'required|numeric|min:0',
        'payment_method' => 'required|in:cash,card,bank_transfer',
    ]);

    $customerId = 1; // TODO: replace with Auth::id()

    // 2️⃣ Create reservation
    $reservation = Reservation::create(array_merge($validated, ['customer_id' => $customerId]));

    // 3️⃣ Determine if using API
    $useApi = (bool)$request->query('use_api', false);

    // 4️⃣ Fetch vehicle consistently
    $vehicleData = $this->getVehicleJson($validated['vehicle_id'], $useApi);
    $vehicle = is_array($vehicleData) ? (object)$vehicleData : $vehicleData;

    // 5️⃣ Safety fallback if vehicle data is missing
    if (!$vehicle) {
        $vehicle = (object)[
            'id' => $validated['vehicle_id'],
            'registration_number' => 'Unknown',
            'brand' => 'Unknown',
            'model' => '',
            'type' => 'Unknown',
            'availability_status' => 'Unknown',
            'rental_price' => 0,
        ];
    }

    // 6️⃣ Update vehicle status
    if ($useApi) {
        Http::post(url($this->vehicleApi . '/update-status'), [
            'vehicle_id' => $validated['vehicle_id'],
            'status'     => 'rented',
        ]);
    } else {
        $this->vehicleService->updateStatus($validated['vehicle_id'], 'rented');
    }

    // 7️⃣ Return Blade view
    return view('reservations.reservationSuccess', [
        'reservation_id' => $reservation->id,
        'vehicle'        => $vehicle,
        'pickup_date'    => $validated['pickup_date'],
        'return_date'    => $validated['return_date'],
        'days'           => $validated['days'],
        'total_cost'     => $validated['total_cost'],
        'payment_method' => ucfirst(str_replace('_', ' ', $validated['payment_method'])),
    ]);
}

   /**
     * Show the current ongoing reservation
     */
    public function myReservations(Request $request)
    {
        $customerId = 1; // TODO: replace with Auth::id()
        $useApi = (bool) $request->query('use_api', false);

        // Fetch the first ongoing reservation
        $reservation = Reservation::where('customer_id', $customerId)
            ->where('status', 'ongoing')
            ->orderBy('pickup_date', 'asc')
            ->first();

        $noOngoing = false;

        if ($reservation) {
            // Ensure vehicle_id exists
            if ($reservation->vehicle_id) {
                // Always fetch internal if from DB
                $vehicleData = $this->getVehicleJson($reservation->vehicle_id, $useApi);

                // Convert JsonResponse to array if needed
                if ($vehicleData instanceof \Illuminate\Http\JsonResponse) {
                    $vehicleData = $vehicleData->getData(true)['data'] ?? null;
                }

                // Convert array to object for Blade compatibility
                $reservation->vehicle = is_array($vehicleData) ? (object) $vehicleData : $vehicleData;

                // Safety check if vehicle data is missing
                if (!$reservation->vehicle) {
                    $reservation->vehicle = (object)[
                        'brand' => 'Unknown',
                        'model' => '',
                        'rental_price' => 0,
                        'type' => 'Unknown'
                    ];
                }

            } else {
                // No vehicle_id in reservation
                $reservation->vehicle = (object)[
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

    /**
 * Show reservation history for a customer
 */
public function history(Request $request)
{
    $customerId = 1; // TODO: replace with Auth::id() for real login

    $reservations = Reservation::where('customer_id', $customerId)
        ->whereIn('status', ['completed', 'cancelled'])
        ->orderBy('return_date', 'desc')
        ->get();

    // Fetch vehicle info for each reservation
    foreach ($reservations as $reservation) {
        if ($reservation->vehicle_id) {
            $vehicleData = $this->getVehicleJson($reservation->vehicle_id, false); // internal service
            $reservation->vehicle = is_array($vehicleData) ? (object)$vehicleData : $vehicleData;
        } else {
            $reservation->vehicle = (object)[
                'brand' => 'Unknown',
                'model' => '',
                'registration_number' => 'Unknown',
            ];
        }
    }

    return view('reservations.reservationHistory', compact('reservations'));
}

}
