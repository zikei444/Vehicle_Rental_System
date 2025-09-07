<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Http; 

class AdminVehicleController extends Controller
{
    private $vehicleApi = 'http://127.0.0.1/Vehicle_Rental_System/public/api/vehicleApi.php';

    public function index()
    {
        // Fetch all vehicles from API
        $response = Http::get($this->vehicleApi, ['action' => 'getAll']);
        $apiVehicles = $response->json()['data'] ?? [];

        // Sync local DB
        foreach ($apiVehicles as $apiVehicle) {
            Vehicle::updateOrCreate(
                ['id' => $apiVehicle['id']],
                [
                    'type' => $apiVehicle['type'],
                    'brand' => $apiVehicle['brand'],
                    'model' => $apiVehicle['model'],
                    'year_of_manufacture' => $apiVehicle['year_of_manufacture'] ?? null,
                    'registration_number' => $apiVehicle['registration_number'] ?? null,
                    'rental_price' => $apiVehicle['rental_price'] ?? 0,
                    'availability_status' => $apiVehicle['availability_status'] ?? 'available',
                    'image' => $apiVehicle['image'] ?? null,
                ]
            );
        }

        $vehicles = Vehicle::with(['car', 'truck', 'van'])->get();
        return view('vehicles.adminIndex', compact('vehicles'));
    }

    // Creating new vehicle
    public function create()
    {
        return view('vehicles.create');
    }

    // Store new vehicle
    public function store(Request $request)
    {
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

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/vehicles'), $imageName);
            $vehicle->update(['image' => $imageName]);
        }
        
        // Handle type-specific details for (car, truck, van)
        if ($vehicle->type === 'car') {
            $request->validate([
                'fuel_type' => 'required|in:petrol,diesel,electric',
                'transmission' => 'required|in:manual,automatic',
                'seats' => 'required|integer|min:1',
                'air_conditioning' => 'nullable|in:yes,no',
                'fuel_efficiency' => 'nullable|numeric|min:0',
            ]);
            $vehicle->car()->create($request->only(['fuel_type', 'transmission', 'seats', 'air_conditioning', 'fuel_efficiency']));
        }

        if ($vehicle->type === 'truck') {
            $request->validate([
                'truck_type' => 'required|string|max:50',
                'load_capacity' => 'required|numeric|min:0',
                'fuel_type' => 'required|in:petrol,diesel',
            ]);
            $vehicle->truck()->create($request->only(['truck_type', 'load_capacity', 'fuel_type']));
        }

        if ($vehicle->type === 'van') {
            $request->validate([
                'passenger_capacity' => 'required|integer|min:1',
                'fuel_type' => 'required|in:petrol,diesel',
                'air_conditioning' => 'nullable|in:yes,no',
            ]);
            $vehicle->van()->create($request->only(['passenger_capacity', 'fuel_type', 'air_conditioning']));
        }

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle added successfully.');
    }

    // Show details of a single vehicle
    public function show($id)
    {
        $vehicle = Vehicle::with(['car','truck','van'])->find($id);
        if (!$vehicle) {
            return redirect()->route('vehicles.index')->with('error','Vehicle not found');
        }
        return view('vehicles.show', [
            'vehicle' => $vehicle,
            'back_route' => route('admin.vehicles.index')
        ]);
    }

    // Edit vehicle
    public function edit($id)
    {
        $vehicle = Vehicle::with(['car', 'truck', 'van'])->find($id);

        if (!$vehicle) {
            return redirect()->route('admin.vehicles.index')->with('error', 'Vehicle not found');
        }

        return view('vehicles.edit', compact('vehicle'));
    }

    // Update vehicle
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'type' => 'required|in:car,truck,van',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year_of_manufacture' => 'required|digits:4|integer|min:1900|max:' . date('Y'),
            'registration_number' => 'required|string|max:50|unique:vehicles,registration_number,' . $id,
            'rental_price' => 'required|numeric|min:0',
            'availability_status' => 'required|in:available,rented,reserved,under_maintenance',
        ]);

        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update($validated);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/vehicles'), $imageName);
            $vehicle->update(['image' => $imageName]);
        }

        // Update type-specific for (car, truck, van)
        if ($vehicle->type === 'car' && $vehicle->car) {
            $request->validate([
                'fuel_type' => 'required|in:petrol,diesel,electric',
                'transmission' => 'required|in:manual,automatic',
                'seats' => 'required|integer|min:1',
                'air_conditioning' => 'nullable|in:yes,no',
                'fuel_efficiency' => 'nullable|numeric|min:0',
            ]);

            $vehicle->car->update($request->only(['fuel_type', 'transmission', 'seats', 'air_conditioning', 'fuel_efficiency']));
        }
        
        if ($vehicle->type === 'truck' && $vehicle->truck) {
            $request->validate([
                'truck_type' => 'required|string|max:50',
                'load_capacity' => 'required|numeric|min:0',
                'fuel_type' => 'required|in:petrol,diesel',
            ]);
            $vehicle->truck->update($request->only(['truck_type', 'load_capacity', 'fuel_type']));
        }

        if ($vehicle->type === 'van' && $vehicle->van) {
            $request->validate([
                'passenger_capacity' => 'required|integer|min:1',
                'fuel_type' => 'required|in:petrol,diesel',
                'air_conditioning' => 'nullable|in:yes,no',
            ]);
            $vehicle->van->update($request->only(['passenger_capacity', 'fuel_type', 'air_conditioning']));
        }

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    // Delete vehicle
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }
}

?>