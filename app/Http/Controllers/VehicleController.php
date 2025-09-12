<?php

// STUDENT NAME: Lian Wei Ying
// STUDENT ID: 23WMR14568

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 
use App\Models\Vehicle;
use App\Services\RatingService;

class VehicleController extends Controller
{
    // API linking
    private string $ratingApi  = '/api/ratings';

    private RatingService $ratingService;

    // To used ratingService function
    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    // Get one vehicle 
    private function getVehicleJson(int $vehicleId): ?array
    {
        $vehicle = Vehicle::with(['car','truck','van'])->find($vehicleId);
        return $vehicle ? $vehicle->toArray() : null;
    }

    // Get rating details 
    private function getRatingSummary(int $vehicleId, bool $useApi): array
    {
        if ($useApi) {
            // Use Api
            $response = Http::get(url($this->ratingApi . "/summary/{$vehicleId}"))->json();
            return $response['data'] ?? ['average' => null, 'count' => 0];
        }

        // Or from local RatingService
        $ratings = $this->ratingService->getVehicleRatingSummary($vehicleId, 'approved');

        // If the result is JSON, take the data inside
        if ($ratings instanceof \Illuminate\Http\JsonResponse) {
            return $ratings->getData(true)['data'] ?? ['average' => null, 'count' => 0];
        }

        return ['average' => null, 'count' => 0];
    }

    // Show all vehicles 
    public function index(Request $request)
    {
        $query = Vehicle::query();

        // Search vehicles (brand or model)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        // Filter vehicles by type (car, truck, van)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Rating count and average (only approved ratings)
        $query->withCount([
            'ratings as ratings_count' => function ($q) {
                $q->approved();
            }
        ])->withAvg([
            'ratings as average_rating' => function ($q) {
                $q->approved();
            }
        ], 'rating');

        // Sort vehicles 
        if ($request->filled('sort_by') && $request->filled('order')) {
            $query->orderBy($request->sort_by, $request->order);
        }

        // Get all vehicles
        $vehicles = $query->get();

        // Show in index page
        return view('vehicles.index', compact('vehicles'));
    }

    // Show one vehicle with its ratings
    public function show(int $vehicleId, Request $request)
    {
        $useApi = (bool) $request->query('use_api', false);

        // Get the vehicle 
        $vehicleData = $this->getVehicleJson($vehicleId);
        if (!$vehicleData) {
            return redirect()->route('vehicles.index')->with('error', 'Vehicle not found');
        }

        // Convert array to object 
        $vehicle = json_decode(json_encode($vehicleData));

        // Add rating summary to vehicle
        $vehicle->ratingSummary = $this->getRatingSummary($vehicle->id, $useApi);

        return view('vehicles.show', [
            'vehicle'   => $vehicle,
            'useApi'    => $useApi,
            'back_route'=> route('vehicles.index'),
        ]);
    }

    // Choose a vehicle and go to reservation page
    public function select($id)
    {
        $vehicle = Vehicle::find($id);

        // If vehicle not found or not available
        if (!$vehicle || $vehicle->availability_status !== 'available') {
            return redirect()->route('vehicles.index')
                ->with('error', 'This vehicle is not available');
        }

        // Go to reservation process
        return redirect()->route('reservation.process', ['vehicle_id' => $id]);
    }
}