<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleApiController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Vehicle::all()
        ]);
    }

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

    public function updateStatus(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'status' => 'required|string'
        ]);

        $vehicle = Vehicle::find($request->vehicle_id);
        $vehicle->availability_status = $request->status;
        $vehicle->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle status updated'
        ]);
    }
}
