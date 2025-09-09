<?php

namespace App\Services\Facade;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\VehicleService; 

class VehicleManagementFacade
{
    private VehicleService $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    // ===== CREATE VEHICLE =====
    public function createVehicle(Request $request): Vehicle
    {
        return DB::transaction(function () use ($request) {
            $validated = $request->validate([
                'type' => 'required|in:car,truck,van',
                'brand' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'year_of_manufacture' => 'required|digits:4|integer|min:1900|max:' . date('Y'),
                'registration_number' => 'required|string|max:50|unique:vehicles,registration_number',
                'rental_price' => 'required|numeric|min:0',
                'availability_status' => 'required|in:available,rented,reserved,under_maintenance',
            ]);

            $vehicle = Vehicle::create($validated);

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/vehicles'), $imageName);
                $vehicle->update(['image' => $imageName]);
            }

            $this->handleTypeSpecificData($vehicle, $request);

            return $vehicle;
        });
    }

    // ===== UPDATE VEHICLE =====
    public function updateVehicle(Request $request, int $id): Vehicle
    {
        return DB::transaction(function () use ($request, $id) {
            $vehicle = Vehicle::findOrFail($id);

            $validated = $request->validate([
                'type' => 'required|in:car,truck,van',
                'brand' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'year_of_manufacture' => 'required|digits:4|integer|min:1900|max:' . date('Y'),
                'registration_number' => 'required|string|max:50|unique:vehicles,registration_number,' . $id,
                'rental_price' => 'required|numeric|min:0',
                'availability_status' => 'required|in:available,rented,reserved,under_maintenance',
            ]);

            $vehicle->update($validated);

            // Update image
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/vehicles'), $imageName);
                $vehicle->update(['image' => $imageName]);
            }

            $this->handleTypeSpecificData($vehicle, $request, true);

            return $vehicle;
        });
    }

    // ===== DELETE VEHICLE =====
    public function deleteVehicle(int $id): void
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();
    }

    // ===== UPDATE STATUS =====
    public function updateStatus(int $id, string $status, ?string $comment = null)
    {
        return $this->vehicleService->updateStatus($id, $status, $comment);
    }

    // ===== HANDLE TYPE-SPECIFIC DATA =====
    private function handleTypeSpecificData(Vehicle $vehicle, Request $request, bool $update = false): void
    {
        if ($vehicle->type === 'car') {
            $request->validate([
                'fuel_type' => 'required|in:petrol,diesel,electric',
                'transmission' => 'required|in:manual,automatic',
                'seats' => 'required|integer|min:1',
                'air_conditioning' => 'nullable|in:yes,no',
                'fuel_efficiency' => 'nullable|numeric|min:0',
            ]);
            $update
                ? $vehicle->car->update($request->only(['fuel_type', 'transmission', 'seats', 'air_conditioning', 'fuel_efficiency']))
                : $vehicle->car()->create($request->only(['fuel_type', 'transmission', 'seats', 'air_conditioning', 'fuel_efficiency']));
        }

        if ($vehicle->type === 'truck') {
            $request->validate([
                'truck_type' => 'required|string|max:50',
                'load_capacity' => 'required|numeric|min:0',
                'fuel_type' => 'required|in:petrol,diesel',
            ]);
            $update
                ? $vehicle->truck->update($request->only(['truck_type', 'load_capacity', 'fuel_type']))
                : $vehicle->truck()->create($request->only(['truck_type', 'load_capacity', 'fuel_type']));
        }

        if ($vehicle->type === 'van') {
            $request->validate([
                'passenger_capacity' => 'required|integer|min:1',
                'fuel_type' => 'required|in:petrol,diesel',
                'air_conditioning' => 'nullable|in:yes,no',
            ]);
            $update
                ? $vehicle->van->update($request->only(['passenger_capacity', 'fuel_type', 'air_conditioning']))
                : $vehicle->van()->create($request->only(['passenger_capacity', 'fuel_type', 'air_conditioning']));
        }
    }
}