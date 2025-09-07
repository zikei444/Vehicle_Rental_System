<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\Reservations\CostCalculator\CarCostStrategy;
use App\Services\Reservations\CostCalculator\TruckCostStrategy;
use App\Services\Reservations\CostCalculator\VanCostStrategy;

class ReservationController extends Controller
{
    private $vehicleApi = 'http://127.0.0.1/Vehicle_Rental_System/public/api/vehicleApi.php'; // api path
    private $reservationApi = 'http://127.0.0.1/Vehicle_Rental_System/public/api/reservationApi.php'; // api path

    public function process(Request $request)
    {
        $vehicle_id = $request->query('vehicle_id');

        // Fetch vehicle info from Vehicle API
        $response = Http::get($this->vehicleApi, [
            'action' => 'get',
            'id' => $vehicle_id
        ]);

        $vehicle = $response->json()['data'] ?? null;

        
        return view('reservations.reservationProcess', compact('vehicle'));
    }

    public function calculateAjax(Request $request)
    {
        $pickup = new \DateTime($request->pickup_date);
        $days = (int) $request->days;

        if ($days <= 0) {
            return response()->json(['error' => 'Number of days must be at least 1']);
        }

        // Calculate return date (pickup + days)
        $return = (clone $pickup)->modify("+{$days} days");

        // Fetch vehicle info
        $response = Http::get($this->vehicleApi, [
            'action' => 'get',
            'id' => $request->vehicle_id
        ]);


        $vehicle = $response->json()['data'] ?? null;

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found']);
        }

        // Pick strategy
        $strategyClass = "App\\Services\\Reservations\\CostCalculator\\" . ucfirst($vehicle['type']) . "CostStrategy";
        $strategy = new $strategyClass();
        $totalCost = $strategy->calculate($vehicle, $days);

        return response()->json([
            'vehicle_id' => $request->vehicle_id,
            'totalCost' => $totalCost,
            'days' => $days,
            'pickup' => $pickup->format('Y-m-d'),
            'return' => $return->format('Y-m-d'),
        ]);
    }


    public function confirm(Request $request)
    {
        // Fetch full vehicle details from API
        $response = Http::get($this->vehicleApi, [
            'action' => 'get',
            'id' => $request->vehicle_id
        ]);

        $vehicle = $response->json()['data'] ?? null;

        $pickup_date = $request->pickup_date;
        $return_date = $request->return_date;
        $days = $request->days;
        $total_cost = $request->total_cost;

        return view('reservations.reservationPayment', compact(
            'vehicle',
            'pickup_date',
            'return_date',
            'days',
            'total_cost'
        ));
    }

    // Handle payment processing
    public function paymentProcess(Request $request)
    {
        
        $payment_method = $request->input('payment_method');

        // Validate common fields
        $rules = [
            'vehicle_id'   => 'required',
            'pickup_date'  => 'required|date',
            'return_date'  => 'required|date',
            'days'         => 'required|integer|min:1',
            'total_cost'   => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,bank_transfer'
        ];

        // Add extra validation if payment method is card
        if ($payment_method === 'card') {
            $rules = array_merge($rules, [
                'card_name'    => 'required|string|max:255',
                'card_number'  => 'required|digits_between:13,19',
                'cvv'          => 'required|digits:3',
                'expiry_month' => 'required|integer|min:1|max:12',
                'expiry_year'  => 'required|integer|min:' . date('Y'),
            ]);
        }

        $validated = $request->validate($rules);

        $response = Http::withBody(
            json_encode([
                'customer_id'    => 1,
                'vehicle_id'     => $validated['vehicle_id'],
                'pickup_date'    => $validated['pickup_date'],
                'return_date'    => $validated['return_date'],
                'days'           => $validated['days'],  // send days
                'total_cost'     => $validated['total_cost'],
                'payment_method' => $validated['payment_method'],
            ]),
            'application/json'
        )->post($this->reservationApi . '?action=add');


        $apiResult = $response->json();

        // After successfully saving reservation
        $response = Http::withBody(
            json_encode([
                'vehicle_id' => $validated['vehicle_id'],
                'availability_status'     => 'rented',
            ]),
            'application/json'
        )->post($this->vehicleApi . '?action=updateStatus');

        // Payment success
        return view('reservations.reservationSuccess', [
            'reservation_id'  => $apiResult['id'] ?? null, 
            'payment_method' => ucfirst(str_replace('_', ' ', $payment_method)),
            'vehicle_id'     => $validated['vehicle_id'],
            'pickup_date'    => $validated['pickup_date'],
            'return_date'    => $validated['return_date'],
            'days'           => $validated['days'],
            'total_cost'     => $validated['total_cost'],
            'card_name'      => $validated['card_name'] ?? null,
            'card_number'    => $validated['card_number'] ?? null,
        ]);
    }

    public function myReservations()
{
    $customerId = 1; // replace with Auth::id() once login is ready

    // Fetch all reservations
    $response = Http::get($this->reservationApi, [
        'action' => 'getAll'
    ]);

    $reservations = $response->json()['data'] ?? [];

    // Filter only reservations for this customer
    $reservations = array_filter($reservations, fn($r) => $r['customer_id'] == $customerId);

    // Fetch vehicle details for each reservation
    foreach ($reservations as &$res) {
        $vehicleResp = Http::get($this->vehicleApi, [
            'action' => 'get',
            'id' => $res['vehicle_id']
        ]);
        $res['vehicle'] = $vehicleResp->json()['data'] ?? null;
    }

    return view('reservations.myReservation', compact('reservations'));
}
}