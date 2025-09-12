<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleApiController extends Controller
{
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

    // Create Vehicle
    public function store(Request $request)
    {
        $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year_of_manufacture' => 'nullable|integer|min:1900|max:' . date('Y'),
            'registration_number' => 'required|string|unique:vehicles,registration_number',
            'rental_price' => 'required|numeric|min:0',
            'availability_status' => 'required|in:available,rented,reserved,under_maintenance',
        ]);

        $vehicle = Vehicle::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle created successfully',
            'data' => $vehicle
        ], 201);
    }

    // Update vehicle
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle not found'
            ], 404);
        }

        $request->validate([
            'brand' => 'sometimes|required|string|max:100',
            'model' => 'sometimes|required|string|max:100',
            'year_of_manufacture' => 'nullable|integer|min:1900|max:' . date('Y'),
            'registration_number' => 'sometimes|required|string|unique:vehicles,registration_number,' . $id,
            'rental_price' => 'sometimes|required|numeric|min:0',
            'availability_status' => 'sometimes|required|in:available,rented,reserved,under_maintenance',
        ]);

        $vehicle->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle updated successfully',
            'data' => $vehicle
        ]);
    }

    // Update vehicle (availability status only)
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

    // Delete 
    public function destroy($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle not found'
            ], 404);
        }

        $vehicle->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle deleted successfully'
        ]);
    }
}