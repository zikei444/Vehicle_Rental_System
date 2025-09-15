<?php

// STUDENT NAME: Lian Wei Ying
// STUDENT ID: 23WMR14568

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleApiController extends Controller
{
    //List all vehicles (paginated)
    public function index()
    { 
        $vehicles = Vehicle::select(
        'id', 'type', 'brand', 'model', 'year_of_manufacture as year', 'registration_number', 'availability_status'
        )->get(); 

        return response()->json([
            'status' => 'success',
            'data'   => $vehicles
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
            'type' => 'required|in:car,truck,van',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year_of_manufacture' => 'nullable|integer|min:1900|max:' . date('Y'),
            'registration_number' => 'required|string|unique:vehicles,registration_number',
            'rental_price' => 'required|numeric|min:0',
            'availability_status' => 'required|in:available,rented,reserved,under_maintenance',
        ]);

        $vehicle = Vehicle::create($request->only([
            'type', 'brand', 'model', 'year_of_manufacture',
            'registration_number', 'rental_price', 'availability_status'
        ]));

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
            'type' => 'sometimes|required|in:car,truck,van',
            'brand' => 'sometimes|required|string|max:100',
            'model' => 'sometimes|required|string|max:100',
            'year_of_manufacture' => 'nullable|integer|min:1900|max:' . date('Y'),
            'registration_number' => 'sometimes|required|string|unique:vehicles,registration_number,' . $id,
            'rental_price' => 'sometimes|required|numeric|min:0',
            'availability_status' => 'sometimes|required|in:available,rented,reserved,under_maintenance',
        ]);

        $vehicle->update($request->only([
            'type','brand','model','year_of_manufacture',
            'registration_number','rental_price','availability_status'
        ]));


        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle updated successfully',
            'data' => $vehicle
        ]);
    }

    // Update vehicle (availability status only)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:available,rented,reserved,under_maintenance'
        ]);

        $vehicle = Vehicle::find($id);
        if (!$vehicle) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle not found'
            ], 404);
        }

        $vehicle->availability_status = $request->status;
        $vehicle->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle status updated successfully',
            'data' => $vehicle
        ], 200);
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

        try {
            // Selete rating_logs linked to vehicle's reservations
            $reservationIds = $vehicle->reservations()->pluck('id');
            if ($reservationIds->count() > 0) {
                $ratingsForReservations = \App\Models\Rating::whereIn('reservation_id', $reservationIds)->pluck('id');
                if ($ratingsForReservations->count() > 0) {
                    \App\Models\RatingLog::whereIn('rating_id', $ratingsForReservations)->delete();
                }
            }

            // Delete ratings linked to reservations 
            if ($reservationIds->count() > 0) {
                \App\Models\Rating::whereIn('reservation_id', $reservationIds)->delete();
            }

            // Delete vehicle's reservations 
            $vehicle->reservations()->delete();

            // Delete ratings directly linked to vehicle 
            if ($vehicle->ratings()->exists()) {
                $vehicleRatingIds = $vehicle->ratings()->pluck('id');
                if ($vehicleRatingIds->count() > 0) {
                    \App\Models\RatingLog::whereIn('rating_id', $vehicleRatingIds)->delete();
                }
                $vehicle->ratings()->delete();
            }

            // Delete maintenance records
            if ($vehicle->maintenanceRecords()->exists()) {
                $vehicle->maintenanceRecords()->delete();
            }

            // Delete related car/truck/van (hasOne) 
            if ($vehicle->car) $vehicle->car->delete();
            if ($vehicle->truck) $vehicle->truck->delete();
            if ($vehicle->van) $vehicle->van->delete();

            // Delete the vehicle itself 
            $vehicle->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete vehicle: ' . $e->getMessage()
            ], 500);
        }
    }
}