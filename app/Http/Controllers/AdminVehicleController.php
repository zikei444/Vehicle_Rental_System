<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Services\Facade\VehicleManagementFacade; // Implement with Facade pattern 

class AdminVehicleController extends Controller
{
    // List all vehicles
    public function index()
    {
        $vehicles = Vehicle::with(['car','truck','van'])->get();
        return view('vehicles.adminIndex', compact('vehicles'));
    }

    // Show form to create  vehicle
    public function create()
    {
        return view('vehicles.create');
    }

    // Store a new vehicle
    public function store(Request $request)
    {
        // Facade call
        VehicleManagementFacade::createVehicle($request); 
        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle added successfully.');
    }

    // Show details of a single vehicle
    public function show($id)
    {
        $vehicle = Vehicle::with(['car','truck','van'])->findOrFail($id);
        return view('vehicles.show', compact('vehicle'));
    }

    // Show form to edit a vehicle
    public function edit($id)
    {
        $vehicle = Vehicle::with(['car','truck','van'])->findOrFail($id);
        return view('vehicles.edit', compact('vehicle'));
    }

    // Update an existing vehicle
    public function update(Request $request, $id)
    {
        // Facade call
        VehicleManagementFacade::updateVehicle($request, $id); 
        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    // Delete a vehicle
    public function destroy($id)
    {
        // Facade call
        VehicleManagementFacade::deleteVehicle($id);
        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }
}