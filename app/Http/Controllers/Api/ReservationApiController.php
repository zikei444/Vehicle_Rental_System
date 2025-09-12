<!-- 
STUDENT NAME: LIEW ZI KEI 
STUDENT ID: 23WMR14570
-->


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;

class ReservationApiController extends Controller
{
    public function index()
    {
        return response()->json(Reservation::all());
    }

    public function show($id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found'], 404);
        }
        return response()->json($reservation);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'vehicle_id'  => 'required|integer|exists:vehicles,id',
            'pickup_date' => 'required|date',
            'return_date' => 'required|date',
            'days'        => 'required|integer|min:1',
            'total_cost'  => 'required|numeric|min:0',
            'status'      => 'required|string|in:ongoing,completed,cancelled',
        ]);

        $reservation = Reservation::create($validated);
        return response()->json($reservation, 201);
    }

    public function update(Request $request, $id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found'], 404);
        }

        $validated = $request->validate([
            'pickup_date' => 'sometimes|date',
            'return_date' => 'sometimes|date',
            'days'        => 'sometimes|integer|min:1',
            'total_cost'  => 'sometimes|numeric|min:0',
            'status'      => 'sometimes|string|in:ongoing,completed,cancelled',
        ]);

        $reservation->update($validated);
        return response()->json($reservation);
    }

    public function destroy($id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found'], 404);
        }

        $reservation->delete();
        return response()->json(['message' => 'Reservation deleted successfully']);
    }

    public function byCustomer($customerId)
    {
        $reservations = Reservation::where('customer_id', $customerId)->get();
        return response()->json(['data' => $reservations]);
    }
}
