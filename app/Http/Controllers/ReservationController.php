<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\Reservations\CostCalculator\CarCostStrategy;
use App\Services\Reservations\CostCalculator\TruckCostStrategy;
use App\Services\Reservations\CostCalculator\VanCostStrategy;

class ReservationController extends Controller
{
    private $vehicleApi = 'http://localhost/vehicle-rental-system/public/api/vehicleApi.php'; // api path

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

    public function calculate(Request $request)
    {
        $vehicle_id = $request->input('vehicle_id');
        $pickup = $request->input('pickup_date');
        $return = $request->input('return_date');

        if (!$vehicle_id || !$pickup || !$return) {
            return redirect()->back()->withErrors(['error' => 'All fields are required']);
        }

        // Fetch vehicle info from API
        $response = Http::get($this->vehicleApi, [
            'action' => 'get',
            'id' => $vehicle_id
        ]);

        $vehicleData = $response->json()['data'] ?? null;
        $vehicle = is_string($vehicleData) ? json_decode($vehicleData, true) : $vehicleData;

        if (!$vehicle) {
            return redirect()->back()->withErrors(['error' => 'Vehicle not found']);
        }

        // Calculate number of days
        $days = max(1, (strtotime($return) - strtotime($pickup)) / (60 * 60 * 24));

        // Choose strategy based on vehicle type
        switch ($vehicle['type']) {
            case 'car':
                $strategy = new CarCostStrategy();
                break;
            case 'truck':
                $strategy = new TruckCostStrategy();
                break;
            case 'van':
                $strategy = new VanCostStrategy();
                break;
            default:
                return redirect()->back()->withErrors(['error' => 'Unknown vehicle type']);
        }

        $totalCost = $strategy->calculate($vehicle, $days);

        // Return to same view with cost
        return view('reservations.reservationProcess', compact('vehicle', 'totalCost', 'pickup', 'return'));
    }
}