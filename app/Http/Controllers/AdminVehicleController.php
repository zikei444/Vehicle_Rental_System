<?php

// STUDENT NAME: Lian Wei Ying
// STUDENT ID: 23WMR14568

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Facade\VehicleManagementFacade; // Facade to access VehicleManagementService

class AdminVehicleController extends Controller
{
    // List all vehicles (with search & filters)
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'type', 'availability_status']);
        $vehicles = VehicleManagementFacade::getAllVehicles($filters);

        return view('vehicles.adminIndex', compact('vehicles'));
    }

    // Show form to create vehicle
    public function create()
    {
        return view('vehicles.create');
    }

    // Store a new vehicle
    public function store(Request $request)
    {
        VehicleManagementFacade::createVehicle($request); 
        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle added successfully.');
    }

    // Show details of a single vehicle
    public function show($id)
    {
        $vehicle = VehicleManagementFacade::getVehicleById($id);
        return view('vehicles.show', compact('vehicle'));
    }

    // Show form to edit a vehicle
    public function edit($id)
    {
        $vehicle = VehicleManagementFacade::getVehicleById($id);
        return view('vehicles.edit', compact('vehicle'));
    }

    // Update an existing vehicle
    public function update(Request $request, $id)
    {
        VehicleManagementFacade::updateVehicle($request, $id); 
        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    // Delete a vehicle
    public function destroy($id)
    {
        VehicleManagementFacade::deleteVehicle($id);
        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }
}