<?php

// STUDENT NAME: LIEW ZI KEI 
// STUDENT ID: 23WMR14570

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Reservation;
use App\Models\Vehicle;
use App\Services\VehicleService;

class AdminReservationController extends Controller
{
    private $vehicleApi = '/api/vehicles';

    private VehicleService $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
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

    // Show all reservations
    public function reservations(Request $request)
    {
        $useApi = (bool) $request->query('use_api', false);
        $reservations = Reservation::all();

        foreach ($reservations as &$res) {
            // Use helper to get vehicle
            $vehicleData = $this->getVehicleJson($res->vehicle_id, $useApi);

            // Convert to object for blade view 
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
        }

        return view('reservations.adminReservations', compact('reservations'));
    }

    // Update reservation status (ongoing to completed or cancelled)
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:completed,cancelled',
        ]);

        $reservation = Reservation::findOrFail($id);
        $reservation->status = $validated['status'];
        $reservation->save();

        $useApi = (bool) $request->query('use_api', false);

        // Update vehicle status back to 'available'
        if ($useApi) {
            Http::post(url($this->vehicleApi . '/update-status'), [
                'vehicle_id' => $reservation->vehicle_id,
                'status' => 'available',
            ]);
        } else {
            $this->vehicleService->updateStatus($reservation->vehicle_id, 'available');
        }

        return redirect()->back()->with('success', 'Reservation status updated.');
    }

    // Delete Reservation
    public function destroy(Request $request, $id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return redirect()->back()->with('error', 'Reservation not found.');
        }

        $useApi = (bool) $request->query('use_api', false);

        // Update vehicle status to 'available' back
        if ($useApi) {
            Http::post(url($this->vehicleApi . '/update-status'), [
                'vehicle_id' => $reservation->vehicle_id,
                'status' => 'available',
            ]);
        } else {
            $this->vehicleService->updateStatus($reservation->vehicle_id, 'available');
        }

        // Delete reservation
        $reservation->delete();

        return redirect()->back()->with('success', 'Reservation deleted and vehicle status updated.');
    }
}
