<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Services\Facade\VehicleManagementFacade;

class AdminVehicleController extends Controller
{
    private VehicleManagementFacade $facade;

    public function __construct(VehicleManagementFacade $facade)
    {
        $this->facade = $facade;
    }

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
        $this->facade->createVehicle($request);
        return redirect()->route('vehicles.index')->with('success', 'Vehicle added successfully.');
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
        $this->facade->updateVehicle($request, $id);
        return redirect()->route('vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    // ====== DELETE VEHICLE ======
    public function destroy($id)
    {
        $this->facade->deleteVehicle($id);
        return redirect()->route('vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }
}