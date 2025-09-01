<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VehicleController extends Controller
{
    private $vehicleApi = 'http://localhost/vehicle-rental-system/public/api/vehicleApi.php'; // api path

    // Show all vehicles for selection
    public function index()
    {
        $response = Http::get($this->vehicleApi, ['action' => 'getAll']);
        $vehicles = $response->json()['data'] ?? [];

        return view('vehicles.index', compact('vehicles'));
    }

    // Go to reservation section to proceed 
    public function select($id)
    {
        // Go to reservationProcess.blade.php
        return redirect()->route('reservation.process', ['vehicle_id' => $id]);
    }
}