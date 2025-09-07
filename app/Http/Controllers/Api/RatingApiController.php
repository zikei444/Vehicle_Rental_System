<?php
// app/Http/Controllers/Api/RatingApiController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RatingService;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class RatingApiController extends Controller {
    protected $ratingService;

    public function __construct(RatingService $ratingService) {
        $this->ratingService = $ratingService;
    }

    public function index($vehicleId) {
        $vehicle = Vehicle::findOrFail($vehicleId);
        return response()->json($this->ratingService->getRating($vehicle));
    }

    public function store(Request $request, $vehicleId) {
        $request->validate(['score' => 'required|integer|min:1|max:5']);
        return response()->json(
            $this->ratingService->addRating($vehicleId, $request->score),
            201
        );
    }
}
