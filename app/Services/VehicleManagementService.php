<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\VehicleService;

class VehicleManagementService
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
                'insurance_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'registration_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'roadtax_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);

            $vehicle = Vehicle::create($validated);

            // Upload image
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/vehicles'), $imageName);
                $vehicle->update(['image' => $imageName]);
            }

            // Upload documents
            $docs = ['insurance_doc', 'registration_doc', 'roadtax_doc'];
            foreach ($docs as $doc) {
                if ($request->hasFile($doc)) {
                    $file = $request->file($doc);
                    $fileName = time() . '_' . $doc . '_' . $file->getClientOriginalName();
                    $file->move(public_path('documents/vehicles'), $fileName);
                    $vehicle->update([$doc => $fileName]);
                }
            }

            // Handle type-specific fields
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
                'insurance_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'registration_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'roadtax_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);

            $vehicle->update($validated);

            // Update image
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/vehicles'), $imageName);
                $vehicle->update(['image' => $imageName]);
            }

            // Update documents
            $docs = ['insurance_doc', 'registration_doc', 'roadtax_doc'];
            foreach ($docs as $doc) {
                if ($request->hasFile($doc)) {
                    $file = $request->file($doc);
                    $fileName = time() . '_' . $doc . '_' . $file->getClientOriginalName();
                    $file->move(public_path('documents/vehicles'), $fileName);
                    $vehicle->update([$doc => $fileName]);
                }
            }

            // Handle type-specific fields
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
    private function handleTypeSpecificData(Vehicle $vehicle, Request $request): void
    {
        if ($vehicle->type === 'car') {
            $request->validate([
                'fuel_type' => 'required|in:petrol,diesel,electric',
                'transmission' => 'required|in:manual,automatic',
                'seats' => 'required|integer|min:1',
                'air_conditioning' => 'nullable|in:yes,no',
                'fuel_efficiency' => 'nullable|numeric|min:0',
            ]);

            $vehicle->car()->updateOrCreate(
                ['vehicle_id' => $vehicle->id],
                $request->only(['fuel_type', 'transmission', 'seats', 'air_conditioning', 'fuel_efficiency'])
            );
        }

        if ($vehicle->type === 'truck') {
            $request->validate([
                'truck_type' => 'required|string|max:50',
                'load_capacity' => 'required|numeric|min:0',
                'fuel_type' => 'required|in:petrol,diesel',
            ]);

            $vehicle->truck()->updateOrCreate(
                ['vehicle_id' => $vehicle->id],
                $request->only(['truck_type', 'load_capacity', 'fuel_type'])
            );
        }

        if ($vehicle->type === 'van') {
            $request->validate([
                'passenger_capacity' => 'required|integer|min:1',
                'fuel_type' => 'required|in:petrol,diesel',
                'air_conditioning' => 'nullable|in:yes,no',
            ]);

            $vehicle->van()->updateOrCreate(
                ['vehicle_id' => $vehicle->id],
                $request->only(['passenger_capacity', 'fuel_type', 'air_conditioning'])
            );
        }
    }
}