<?php

// STUDENT NAME: Lian Wei Ying
// STUDENT ID: 23WMR14568

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;

class VehicleService
{
    /**
     * Return all vehicles as JSON
     */
    public function all(): JsonResponse
    {
        $vehicles = Vehicle::all()->map(fn($v) => $this->formatVehicle($v));
        return response()->json(['data' => $vehicles]);
    }

    /**
     * Return single vehicle by ID as JSON
     */
    public function find(int $id): JsonResponse
    {
        $vehicle = Vehicle::find($id);
        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        return response()->json(['data' => $this->formatVehicle($vehicle)]);
    }

    /**
     * Create a new vehicle
     */
    public function create(array $data): JsonResponse
    {
        $vehicle = Vehicle::create($data);
        return response()->json(['status' => 'success', 'data' => $this->formatVehicle($vehicle)], 201);
    }

    /**
     * Update vehicle details
     */
    public function update(Request $request, $id)
    {
        // Validate input (unique rule ignores current record)
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'brand' => 'sometimes|string|max:255',
            'model' => 'sometimes|string|max:255',
            'type' => 'sometimes|string',
            'registration_number' => 'sometimes|string|unique:vehicles,registration_number,' . $id,
            'rental_price' => 'sometimes|numeric|min:0',
        ]);

        return $this->vehicleService->update($id, $request->all());
    }

    /**
     * Update vehicle status and return JSON
     */
    public function updateStatus(int $id, string $status, ?string $comment = null): JsonResponse
    {
        $vehicle = Vehicle::find($id);
        if (!$vehicle) {
            return response()->json(['status' => 'error', 'message' => 'Vehicle not found'], 404);
        }

        $vehicle->availability_status = $status;
        if ($comment !== null) $vehicle->comment = $comment;
        $vehicle->save();

        return response()->json(['status' => 'success', 'vehicle' => $this->formatVehicle($vehicle)]);
    }

    private function formatVehicle(Vehicle $v): array
    {
        return [
            'id' => $v->id,
            'name' => $v->name,
            'brand' => $v->brand,
            'model' => $v->model,
            'type' => $v->type,
            'year' => $v->year_of_manufacture,
            'registration_number' => $v->registration_number,
            'availability_status' => $v->availability_status,
            'rental_price' => $v->rental_price,
            'created_at' => $v->created_at?->toDateTimeString(),
            'updated_at' => $v->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * Delete a vehicle record
     */
    public function destroy($id)
    {
        return $this->vehicleService->delete($id);
    }
}