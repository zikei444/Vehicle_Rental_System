<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\VehicleService;

class VehicleManagementService
{
    private VehicleService $vehicleService;

    // To used vehicleService function 
    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    // Create vehicle
    public function createVehicle(Request $request): Vehicle
    {
        // Use DB transaction 
        return DB::transaction(function () use ($request) {
            
            // Validate basic input
            $validated = $request->validate([
                'type' => 'required|in:car,truck,van',
                'brand' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'year_of_manufacture' => 'required|digits:4|integer|min:1900|max:' . date('Y'),
                'registration_number' => 'required|string|max:50|unique:vehicles,registration_number',
                'rental_price' => 'required|numeric|min:0',
                'availability_status' => 'required|in:available,rented,reserved,under_maintenance',
                'image' => 'required|file|mimes:jpg,jpeg,png|max:5120',
                'insurance_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'registration_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'roadtax_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);

            // Create vehicle record
            $vehicle = Vehicle::create($validated);

            // Upload and save image file
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/vehicles'), $imageName);
                $vehicle->update(['image' => $imageName]);
            }

            //  Upload and save other documents (insurance, registration, roadtax)
            $docs = ['insurance_doc', 'registration_doc', 'roadtax_doc'];
            foreach ($docs as $doc) {
                if ($request->hasFile($doc)) {
                    $file = $request->file($doc);
                    $fileName = time() . '_' . $doc . '_' . $file->getClientOriginalName();
                    $file->move(public_path('documents/vehicles'), $fileName);
                    $vehicle->update([$doc => $fileName]);
                }
            }

            // Save tyepe-specific data
            $this->handleTypeSpecificData($vehicle, $request);

            return $vehicle;
        });
    }

    // Update vehicle
    public function updateVehicle(Request $request, int $id): Vehicle
    {
        return DB::transaction(function () use ($request, $id) {
            
            // Get vehicle
            $vehicle = Vehicle::findOrFail($id);

            // Validate casic input
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

            // Update vehicle info
            $vehicle->update($validated);

            // Update image if a new one is uploaded
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/vehicles'), $imageName);
                $vehicle->update(['image' => $imageName]);
            }

            // Update optional documents if new ones are uploaded
            $docs = ['insurance_doc', 'registration_doc', 'roadtax_doc'];
            foreach ($docs as $doc) {
                if ($request->hasFile($doc)) {
                    $file = $request->file($doc);
                    $fileName = time() . '_' . $doc . '_' . $file->getClientOriginalName();
                    $file->move(public_path('documents/vehicles'), $fileName);
                    $vehicle->update([$doc => $fileName]);
                }
            }

            // Update type-specific data
            $this->handleTypeSpecificData($vehicle, $request, true);

            return $vehicle;
        });
    }

    // Delete vehicle
    public function deleteVehicle(int $id): void
    {
        // Find vehicle and delete action 
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();
    }

    // Update status 
    public function updateStatus(int $id, string $status, ?string $comment = null)
    {
        // Delegate to VehicleService
        return $this->vehicleService->updateStatus($id, $status, $comment);
    }

    // Handle type-specific data
    private function handleTypeSpecificData(Vehicle $vehicle, Request $request): void
    {
        // Car
        if ($vehicle->type === 'car') {
            $request->validate([
                'fuel_type' => 'required|in:petrol,diesel,electric',
                'transmission' => 'required|in:manual,automatic',
                'seats' => 'required|integer|min:1',
                'air_conditioning' => 'nullable|in:yes,no',
                'fuel_efficiency' => 'nullable|numeric|min:0',
            ]);

            // Save or update related car record
            $vehicle->car()->updateOrCreate(
                ['vehicle_id' => $vehicle->id],
                $request->only(['fuel_type', 'transmission', 'seats', 'air_conditioning', 'fuel_efficiency'])
            );
        }

        // Truck
        if ($vehicle->type === 'truck') {
            $request->validate([
                'truck_type' => 'required|string|max:50',
                'load_capacity' => 'required|numeric|min:0',
                'fuel_type' => 'required|in:petrol,diesel',
            ]);

            // Save or update related truck record
            $vehicle->truck()->updateOrCreate(
                ['vehicle_id' => $vehicle->id],
                $request->only(['truck_type', 'load_capacity', 'fuel_type'])
            );
        }

        // Van
        if ($vehicle->type === 'van') {
            $request->validate([
                'passenger_capacity' => 'required|integer|min:1',
                'fuel_type' => 'required|in:petrol,diesel',
                'air_conditioning' => 'nullable|in:yes,no',
            ]);

            // Save or update related van record
            $vehicle->van()->updateOrCreate(
                ['vehicle_id' => $vehicle->id],
                $request->only(['passenger_capacity', 'fuel_type', 'air_conditioning'])
            );
        }
    }
}