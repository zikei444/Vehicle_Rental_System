<?php

// STUDENT NAME: Lian Wei Ying
// STUDENT ID: 23WMR14568

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Services\Facade\VehicleManagementFacade; // Implement with Facade pattern 

class AdminVehicleController extends Controller
{
    // List all vehicles
    public function index(Request $request)
    {
        $query = Vehicle::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                ->orWhere('model', 'like', "%{$search}%")
                ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by availability status
        if ($request->filled('availability_status')) {
            $query->where('availability_status', $request->input('availability_status'));
        }

        $vehicles = $query->with(['car','truck','van'])->get();

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