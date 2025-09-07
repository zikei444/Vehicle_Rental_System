<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
<<<<<<< HEAD
use Illuminate\Support\Facades\Http;
=======
>>>>>>> 4237f6c7827c954e409a66df53c3acf6267c0be0
use App\Models\Vehicle;

class VehicleController extends Controller
{
<<<<<<< HEAD
    private $vehicleApi = 'http://127.0.0.1/Vehicle_Rental_System/public/api/vehicleApi.php';

    public function index(Request $request)
    {
        // Fetch all vehicles from API
        $response = Http::get($this->vehicleApi, ['action' => 'getAll']);
        $apiVehicles = $response->json()['data'] ?? [];

        // Sync local DB with API
        foreach ($apiVehicles as $apiVehicle) {
            $vehicle = Vehicle::updateOrCreate(
                ['id' => $apiVehicle['id']],
                [
                    'type' => $apiVehicle['type'],
                    'brand' => $apiVehicle['brand'],
                    'model' => $apiVehicle['model'],
                    'year_of_manufacture' => $apiVehicle['year_of_manufacture'] ?? null,
                    'registration_number' => $apiVehicle['registration_number'] ?? null,
                    'rental_price' => $apiVehicle['rental_price'] ?? 0,
                    'availability_status' => $apiVehicle['availability_status'] ?? 'available',
                    'image' => $apiVehicle['image'] ?? null,
                ]
            );
        }

        // Filter, search, sort 
        $query = Vehicle::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(fn($q) => $q->where('brand', 'like', "%{$search}%")
                                      ->orWhere('model', 'like', "%{$search}%"));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('sort_by') && $request->filled('order')) {
            $query->orderBy($request->sort_by, $request->order);
        }

        $vehicles = $query->get();

        return view('vehicles.index', compact('vehicles'));
    }

    // Show details of a single vehicle
    public function show($id)  
    {
        $vehicle = Vehicle::with(['car','truck','van'])->find($id);

        if (!$vehicle) {
            return redirect()->route('vehicles.index')->with('error','Vehicle not found');
        }

        return view('vehicles.show', [
            'vehicle' => $vehicle,
            'back_route' => route('vehicles.index')
        ]);
    }

    public function select($id)
    {
        // Fetch vehicle from API
        $response = Http::get($this->vehicleApi, ['action' => 'get', 'id' => $id]);
        $vehicle = $response->json()['data'] ?? null;

        if (!$vehicle || $vehicle['availability_status'] !== 'available') {
            return redirect()->route('vehicles.index')->with('error', 'This vehicle is not available');
        }

        // Redirect to reservation process
        return redirect()->route('reservation.process', ['vehicle_id' => $id]);
    }

}
=======
    /**
     * 显示所有车辆（返回 Blade 页面）
     */
    public function index()
    {
        $vehicles = Vehicle::all();
        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * 显示单个车辆详情（返回 Blade 页面）
     */
    public function show($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return redirect()->back()->with('error', 'Vehicle not found.');
        }

        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * 选择某辆车，跳转到预订流程
     */
    public function select($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return redirect()->back()->with('error', 'Vehicle not found.');
        }

        // 假设你有一个 reservation.process 路由
        return redirect()->route('reservation.process', ['vehicle_id' => $id]);
    }
}
>>>>>>> 4237f6c7827c954e409a66df53c3acf6267c0be0
