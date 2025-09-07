<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Reservation;
use App\Models\Vehicle;
use App\Services\Reservations\CostCalculator\CarCostStrategy;
use App\Services\Reservations\CostCalculator\TruckCostStrategy;
use App\Services\Reservations\CostCalculator\VanCostStrategy;

class ReservationController extends Controller
{
    private $vehicleApi = '/api/vehicles';

    public function process(Request $request)
    {
        $vehicleId = $request->query('vehicle_id');
        $useApi = $request->query('use_api', false);

        try {
            if ($useApi) {
                // External API consumption
                $response = Http::timeout(10)->get(url($this->vehicleApi . '/' . $vehicleId));
                if ($response->failed()) {
                    throw new \Exception('Failed to fetch vehicle from API');
                }
                $vehicle = $response->json()['data'] ?? null;
            } else {
                // Internal service (Eloquent)
                $vehicle = Vehicle::find($vehicleId);
            }

            if (!$vehicle) {
                return back()->with('error', 'Vehicle not found');
            }

            return view('reservations.reservationProcess', compact('vehicle'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Calculate cost via Ajax.
     */
    public function calculateAjax(Request $request)
    {
        $pickup = new \DateTime($request->pickup_date);
        $days = (int) $request->days;

        if ($days <= 0) {
            return response()->json(['error' => 'Number of days must be at least 1']);
        }

        $return = (clone $pickup)->modify("+{$days} days");
        $useApi = $request->query('use_api', false);

        try {
            if ($useApi) {
                $response = Http::timeout(10)->get(url($this->vehicleApi . '/' . $request->vehicle_id));
                if ($response->failed()) {
                    throw new \Exception('Failed to fetch vehicle');
                }
                $vehicle = $response->json()['data'] ?? null;
            } else {
                $vehicle = Vehicle::find($request->vehicle_id);
            }

            if (!$vehicle) {
                return response()->json(['error' => 'Vehicle not found']);
            }

            // Strategy pattern
            $strategyClass = "App\\Services\\Reservations\\CostCalculator\\" . ucfirst($vehicle['type']) . "CostStrategy";
            $strategy = new $strategyClass();
            $totalCost = $strategy->calculate($vehicle, $days);

            return response()->json([
                'vehicle_id' => $request->vehicle_id,
                'totalCost'  => $totalCost,
                'days'       => $days,
                'pickup'     => $pickup->format('Y-m-d'),
                'return'     => $return->format('Y-m-d'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * Confirm reservation before payment.
     */
    public function confirm(Request $request)
    {
        $useApi = $request->query('use_api', false);

        try {
            if ($useApi) {
                $response = Http::timeout(10)->get(url($this->vehicleApi . '/' . $request->vehicle_id));
                if ($response->failed()) {
                    throw new \Exception('Failed to fetch vehicle');
                }
                $vehicle = $response->json()['data'] ?? null;
            } else {
                $vehicle = Vehicle::find($request->vehicle_id);
            }

            if (!$vehicle) {
                return back()->with('error', 'Vehicle not found');
            }

            return view('reservations.reservationPayment', [
                'vehicle'      => $vehicle,
                'pickup_date'  => $request->pickup_date,
                'return_date'  => $request->return_date,
                'days'         => $request->days,
                'total_cost'   => $request->total_cost
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Payment processing + reservation creation.
     */
    public function paymentProcess(Request $request)
    {
        $payment_method = $request->input('payment_method');

        $rules = [
            'vehicle_id'     => 'required',
            'pickup_date'    => 'required|date',
            'return_date'    => 'required|date',
            'days'           => 'required|integer|min:1',
            'total_cost'     => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,bank_transfer'
        ];

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

        // Save reservation
        $reservation = Reservation::create([
            'customer_id'    => 1, // TODO: replace with Auth::id()
            'vehicle_id'     => $validated['vehicle_id'],
            'pickup_date'    => $validated['pickup_date'],
            'return_date'    => $validated['return_date'],
            'days'           => $validated['days'],
            'total_cost'     => $validated['total_cost'],
            'payment_method' => $validated['payment_method'],
        ]);

        // Update vehicle status
        $useApi = $request->query('use_api', false);

        if ($useApi) {
            Http::timeout(10)->post(url($this->vehicleApi . '/update-status'), [
                'vehicle_id' => $validated['vehicle_id'],
                'status'     => 'rented',
            ]);
        } else {
            $vehicle = Vehicle::find($validated['vehicle_id']);
            if ($vehicle) {
                $vehicle->availability_status = 'rented';
                $vehicle->save();
            }
        }

        return view('reservations.reservationSuccess', [
            'reservation_id'  => $reservation->id,
            'payment_method'  => ucfirst(str_replace('_', ' ', $payment_method)),
            'vehicle_id'      => $validated['vehicle_id'],
            'pickup_date'     => $validated['pickup_date'],
            'return_date'     => $validated['return_date'],
            'days'            => $validated['days'],
            'total_cost'      => $validated['total_cost'],
            'card_name'       => $validated['card_name'] ?? null,
            'card_number'     => $validated['card_number'] ?? null,
        ]);
    }

    /**
     * Show reservations of logged-in customer.
     */
    public function myReservations(Request $request)
    {
        $customerId = 1; // TODO: replace with Auth::id()
        $useApi = $request->query('use_api', false);

        $reservations = Reservation::where('customer_id', $customerId)->get();

        foreach ($reservations as &$res) {
            if ($useApi) {
                $vehicleResp = Http::timeout(10)->get(url($this->vehicleApi . '/' . $res->vehicle_id));
                $res->vehicle = $vehicleResp->json()['data'] ?? null;
            } else {
                $res->vehicle = Vehicle::find($res->vehicle_id);
            }
        }

        return view('reservations.myReservation', compact('reservations'));
    }
}
