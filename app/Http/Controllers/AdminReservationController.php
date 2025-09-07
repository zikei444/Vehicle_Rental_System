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

    // Delete Reservation safely and update vehicle status
    public function destroy(Request $request, $id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return redirect()->back()->with('error', 'Reservation not found.');
        }

        $useApi = $request->query('use_api', false);

        // Update vehicle status to 'available' if it exists
        if ($useApi) {
            Http::post(url($this->vehicleApi . '/update-status'), [
                'vehicle_id' => $reservation->vehicle_id,
                'status'     => 'available',
            ]);
        } else {
            $vehicle = Vehicle::find($reservation->vehicle_id);
            if ($vehicle && $vehicle->availability_status !== 'available') {
                $vehicle->availability_status = 'available';
                $vehicle->save();
            }
        }

        // Delete reservation
        $reservation->delete();

        return redirect()->back()->with('success', 'Reservation deleted and vehicle status updated.');
    }

}
