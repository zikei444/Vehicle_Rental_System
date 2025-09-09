<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 
use App\Models\Vehicle;
use App\Services\RatingService;

class VehicleController extends Controller
{
    private string $ratingApi  = '/api/ratings';
    private string $vehicleApi = '/api/vehicles';

    private RatingService $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    private function getVehicleJson(int $vehicleId, bool $useApi): ?array
    {
        if ($useApi) {
            $response = Http::timeout(10)->get(url($this->vehicleApi . '/' . $vehicleId));
            if ($response->failed()) return null;
            return $response->json()['data'] ?? null;
        }

        $vehicle = Vehicle::with(['car','truck','van'])->find($vehicleId);
        return $vehicle ? $vehicle->toArray() : null;
    }

    private function getRatingSummary(int $vehicleId, bool $useApi): array
    {
        if ($useApi) {
            $response = Http::get(url($this->ratingApi . "/summary/{$vehicleId}"))->json();
            return $response['data'] ?? ['average' => null, 'count' => 0];
        }

        $jsonResponse = $this->ratingService->getVehicleRatingSummary($vehicleId);
        if ($jsonResponse instanceof \Illuminate\Http\JsonResponse) {
            return $jsonResponse->getData(true)['data'] ?? ['average' => null, 'count' => 0];
        }

        return ['average' => null, 'count' => 0];
    }

    public function index(Request $request)
    {
        $query = Vehicle::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('sort_by') && $request->filled('order')) {
            $query->orderBy($request->sort_by, $request->order);
        }

        $vehicles = $query->get();

        $vehicles = $vehicles->map(function ($v) {
            $ratingSummary = $this->ratingService
                ->getVehicleRatingSummary($v->id)
                ->getData(true)['data'];

            $v->average_rating = $ratingSummary['average'];
            $v->ratings_count  = $ratingSummary['count'];
            return $v;
        });

        return view('vehicles.index', compact('vehicles'));
    }

    public function show(int $vehicleId, Request $request)
    {
        $useApi = (bool) $request->query('use_api', false);

        $vehicleData = $this->getVehicleJson($vehicleId, $useApi);
        if (!$vehicleData) {
            return redirect()->route('vehicles.index')->with('error', 'Vehicle not found');
        }

        $vehicle = json_decode(json_encode($vehicleData));
        $vehicle->ratingSummary = $this->getRatingSummary($vehicle->id, $useApi);

        return view('vehicles.show', [
            'vehicle' => $vehicle,
            'useApi' => $useApi,
            'back_route' => route('vehicles.index'),
        ]);
    }

    public function select($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle || $vehicle->availability_status !== 'available') {
            return redirect()->route('vehicles.index')
                ->with('error', 'This vehicle is not available');
        }

        return redirect()->route('reservation.process', ['vehicle_id' => $id]);
    }
}