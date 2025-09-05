<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Reservation;

class AdminReservationController extends Controller
{
    private $vehicleApi = 'http://127.0.0.1/Vehicle_Rental_System/public/api/vehicleApi.php';

    // View all reservations
    public function reservations()
    {
        $reservations = Reservation::all();

        // Fetch vehicle info via API for each reservation
        foreach ($reservations as &$res) {
            $vehicleResp = Http::get($this->vehicleApi, [
                'action' => 'get',
                'id' => $res->vehicle_id
            ]);
            $res->vehicle = $vehicleResp->json()['data'] ?? null;
        }

        return view('reservations.adminReservations', compact('reservations'));
    }

    // Update reservation status
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:completed,cancelled',
        ]);

        $reservation = Reservation::find($id);

        if (!$reservation) {
            return redirect()->back()->with('error', 'Reservation not found.');
        }

        $reservation->status = $validated['status'];
        $reservation->save();

        // Update vehicle status via API
        $vehicleResponse = Http::withBody(
            json_encode([
                'vehicle_id' => $reservation->vehicle_id,
                'status'     => 'available'
            ]),
            'application/json'
        )->post($this->vehicleApi . "?action=updateStatus");

        if (!$vehicleResponse->successful()) {
            return redirect()->back()->with('error', 'Reservation updated, but failed to update vehicle status.');
        }

        return redirect()->back()->with('success', 'Reservation and vehicle status updated.');
    }

    // Delete reservation
    public function destroy($id)
    {
        $reservation = Reservation::find($id);

        if ($reservation) {
            $reservation->delete();
        }

        return redirect()->back()->with('success', 'Reservation deleted.');
    }
}
