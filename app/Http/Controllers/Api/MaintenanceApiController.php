<?php

// STUDENT NAME: Loh Yun Le
// STUDENT ID: 23WMR14583

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Maintenance;

class MaintenanceApiController extends Controller
{
    // List maintenances with search, filter & sort
    public function index()
    {
        return response()->json(Maintenance::all());
    }

    // Retrieve single record
    public function show($id)
    {
        $maintenance = Maintenance::find($id);
        if (!$maintenance) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }
        return response()->json($maintenance);
    }

    // Create new record
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'       => 'required|integer|exists:vehicles,id',
            'maintenance_type' => 'required|string|max:50',
            'service_date'     => 'required|date|after_or_equal:today',
            'cost'             => 'required|numeric|min:0',
            'status'           => 'required|string|in:Scheduled,Completed,Cancelled',
            'notes'            => 'nullable|string|max:500',
        ]);

        $maintenance = \App\Models\Maintenance::create($validated);
        return response()->json($maintenance, 201);
    }

    // Update maintenance record using service layer
    public function update(Request $request, $id)
    {
        $maintenance = Maintenance::find($id);
        if (!$maintenance) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }

        $validated = $request->validate([
            'maintenance_type' => 'sometimes|string|max:50',
            'service_date'     => 'sometimes|date',
            'cost'             => 'sometimes|numeric|min:0',
            'status'           => 'sometimes|string|in:Scheduled,Completed,Cancelled',
            'notes'            => 'nullable|string|max:500',
        ]);

        $maintenance->update($validated);
        return response()->json($maintenance);
    }

    // Delete a record
    public function destroy($id)
    {
        $maintenance = Maintenance::find($id);
        if (!$maintenance) {
            return response()->json(['error' => 'Maintenance not found'], 404);
        }

        $maintenance->delete();
        return response()->json(['message' => 'Maintenance deleted successfully']);
    }

    // List maintenance records for a specific vehicle
    public function byVehicle(Request $request, int $vehicleId)
    {
        $records = Maintenance::where('vehicle_id', $vehicleId)->get();
        return response()->json(['data' => $records]);
    }
}