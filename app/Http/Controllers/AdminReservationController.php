<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminReservationController extends Controller
{
    private $reservationApi = 'http://localhost/vehicle-rental-system/public/api/reservationApi.php';
    private $vehicleApi = 'http://localhost/vehicle-rental-system/public/api/vehicleApi.php';

    // View all reservations
    public function reservations()
    {
        $response = Http::get($this->reservationApi, [
            'action' => 'getAll'
        ]);

        $reservations = $response->json()['data'] ?? [];

        return view('reservations.adminReservations', compact('reservations'));
    }

    // Update reservation status
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:completed,cancelled',
        ]);

        // Get reservation to find vehicle_id
        $resResponse = Http::get($this->reservationApi, ['action' => 'get', 'id' => $id]);
        $reservation = $resResponse->json()['data'] ?? null;

        if(!$reservation){
            return redirect()->back()->with('error', 'Reservation not found.');
        }

        $vehicleId = $reservation['vehicle_id'];

        // Update reservation status via PUT
        $response = Http::withBody(
            json_encode(['status' => $validated['status']]),
            'application/json'
        )->put($this->reservationApi . "?action=edit&id={$id}");

        if(!$response->successful()){
            return redirect()->back()->with('error', 'Failed to update reservation.');
        }

        // Update vehicle status to available
        $vehicleResponse = Http::withBody(
            json_encode([
                'status' => 'available',
                'vehicle_id' => $vehicleId
            ]),
            'application/json'
        )->post($this->vehicleApi . "?action=updateStatus");

        if(!$vehicleResponse->successful()){
            return redirect()->back()->with('error', 'Reservation updated, but failed to update vehicle status.');
        }

        return redirect()->back()->with('success', 'Reservation and vehicle status updated.');
    }


    // Delete reservation
    public function destroy($id)
    {
        $response = Http::delete($this->reservationApi . "?action=delete&id={$id}");
        return redirect()->back()->with('success', 'Reservation deleted.');
    }
}
