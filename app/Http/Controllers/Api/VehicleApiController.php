<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleApiController extends Controller
{
    public function __construct()
    {
        // Require authentication (Sanctum or Passport depending on your setup)
        $this->middleware('auth:sanctum');
    }

    //List all vehicles (paginated)
    public function index()
    {
        $vehicles = Vehicle::paginate(10); 

        return response()->json([
            'status' => 'success',
            'data' => $vehicles
        ]);
    }

    // Show single vehicle by ID
    public function show($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $vehicle
        ]);
    }

    
    // Update vehicle availability status
    public function updateStatus(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'status' => 'required|in:available,rented,reserved,under_maintenance'
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        $vehicle->availability_status = $request->status;
        $vehicle->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle status updated successfully',
            'data' => $vehicle
        ]);
    }
}
