<?php

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
            'registration_number' => $v->registration_number,
            'availability_status' => $v->availability_status,
            'rental_price' => $v->rental_price,
            'created_at' => $v->created_at?->toDateTimeString(),
            'updated_at' => $v->updated_at?->toDateTimeString(),
        ];
    }
}
