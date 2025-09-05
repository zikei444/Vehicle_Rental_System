<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    // Show all vehicles for selection
    public function index()
    {
        // Use ORM instead of API
        $vehicles = Vehicle::all();

        return view('vehicles.index', compact('vehicles'));
    }

    // Go to reservation section to proceed 
    public function select($id)
    {
        // Check if vehicle exists
        $vehicle = Vehicle::find($id);
        if (!$vehicle) {
            return redirect()->back()->with('error', 'Vehicle not found.');
        }

        // Redirect to reservation process
        return redirect()->route('reservation.process', ['vehicle_id' => $id]);
    }
}
