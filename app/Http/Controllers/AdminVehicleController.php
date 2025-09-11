<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Services\Facade\VehicleManagementFacade;


class AdminVehicleController extends Controller
{
    // ====== LISTING ======
    public function index()
    {
        $vehicles = Vehicle::with(['car','truck','van'])->get();
        return view('vehicles.adminIndex', compact('vehicles'));
    }

    // ====== SHOW CREATE FORM ======
    public function create()
    {
        return view('vehicles.create');
    }

    // ====== STORE VEHICLE ======
    public function store(Request $request)
    {
        VehicleManagementFacade::createVehicle($request); // Facade call
        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle added successfully.');
    }

    // ====== SHOW SINGLE VEHICLE ======
    public function show($id)
    {
        $vehicle = Vehicle::with(['car','truck','van'])->findOrFail($id);
        return view('vehicles.show', compact('vehicle'));
    }

    // ====== SHOW EDIT FORM ======
    public function edit($id)
    {
        $vehicle = Vehicle::with(['car','truck','van'])->findOrFail($id);
        return view('vehicles.edit', compact('vehicle'));
    }

    // ====== UPDATE VEHICLE ======
    public function update(Request $request, $id)
    {
        VehicleManagementFacade::updateVehicle($request, $id); // Facade call
        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    // ====== DELETE VEHICLE ======
    public function destroy($id)
    {
        VehicleManagementFacade::deleteVehicle($id); // Facade call
        return redirect()->route('vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }
}