<!-- 
STUDENT NAME: LIEW ZI KEI 
STUDENT ID: 23WMR14570
-->

<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Http\JsonResponse;


class ReservationService
{
    /**
     * Return all reservations for a customer in JSON
     */
    public function allByCustomer(int $customerId): JsonResponse
    {
        $reservations = Reservation::where('customer_id', $customerId)
            ->orderBy('pickup_date', 'desc')
            ->get()
            ->map(fn($r) => $this->formatReservation($r));

        return response()->json(['data' => $reservations]);
    }

    /**
     * Return single reservation by ID as JSON
     */
    public function find(int $id): JsonResponse
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found'], 404);
        }

        return response()->json(['data' => $this->formatReservation($reservation)]);
    }

    /**
     * Create a reservation and return as JSON
     */
    public function create(array $data): JsonResponse
    {
        $reservation = Reservation::create($data);
        return response()->json(['data' => $this->formatReservation($reservation)], 201);
    }

    /**
     * Update reservation by ID and return as JSON
     */
    public function update(int $id, array $data): JsonResponse
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found'], 404);
        }

        $reservation->update($data);
        return response()->json(['data' => $this->formatReservation($reservation)]);
    }

    /**
     * Delete reservation by ID and return JSON
     */
    public function delete(int $id): JsonResponse
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found'], 404);
        }

        $reservation->delete();
        return response()->json(['status' => 'success', 'message' => 'Reservation deleted']);
    }

    /**
     * Format reservation for JSON response
     */
    private function formatReservation(Reservation $r): array
    {
        return [
            'id' => $r->id,
            'customer_id' => $r->customer_id,
            'vehicle_id' => $r->vehicle_id,
            'pickup_date' => $r->pickup_date,
            'return_date' => $r->return_date,
            'days' => $r->days,
            'total_cost' => $r->total_cost,
            'status' => $r->status,
            'created_at' => $r->created_at?->toDateTimeString(),
            'updated_at' => $r->updated_at?->toDateTimeString(),
        ];
    }
}
