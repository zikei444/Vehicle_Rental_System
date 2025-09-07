<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Reservation;
use App\Models\Vehicle; 

class AdminReservationController extends Controller
{
    private $vehicleApi = '/api/vehicles';

    // Show all reservations
    public function reservations(Request $request)
    {
        $useApi = $request->query('use_api', false);
        $reservations = Reservation::all();

        foreach ($reservations as &$res) {
            if ($useApi) {
                // External API consumption
                $vehicleResp = Http::get(url($this->vehicleApi . '/' . $res->vehicle_id));
                $res->vehicle = $vehicleResp->json()['data'] ?? null;
            } else {
                // Internal consumption (direct model)
                $res->vehicle = Vehicle::find($res->vehicle_id);
            }
        }

        return view('reservations.adminReservations', compact('reservations'));
    }

    // Update reservation status= ongoing to completed // cancelled
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

        $useApi = $request->query('use_api', false);

        if ($useApi) {
            // External API call
            $vehicleResponse = Http::post(url($this->vehicleApi . '/update-status'), [
                'vehicle_id' => $reservation->vehicle_id,
                'status'     => 'available',
            ]);

            if (!$vehicleResponse->successful()) {
                return redirect()->back()->with('error', 'Reservation updated, but failed to update vehicle status via API.');
            }
        } else {
            // Internal consumption
            $vehicle = Vehicle::find($reservation->vehicle_id);
            if ($vehicle) {
                $vehicle->availability_status = 'available';
                $vehicle->save();
            }
        }

        return redirect()->back()->with('success', 'Reservation and vehicle status updated.');
    }

    // Delete Reservation
    public function destroy($id)
    {
        $reservation = Reservation::find($id);

        if ($reservation) {
            $reservation->delete();
        }

        return redirect()->back()->with('success', 'Reservation deleted.');
    }
}
