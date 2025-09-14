<?php
// STUDENT NAME: Kek Xin Ying
// STUDENT ID: 23WMR14547

namespace App\Http\Controllers\Api;

use App\Models\Rating;
use App\Http\Controllers\Controller;
use App\Services\RatingService;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class RatingApiController extends Controller {
    protected $ratingService;

    public function __construct(RatingService $ratingService) {
        $this->ratingService = $ratingService;
    }

    /**
     * Get rating summary for a vehicle
     */
    public function summary($vehicleId) {
        $summary = $this->ratingService->getVehicleRatingSummary($vehicleId, 'approved');
        return $summary;
    }

    /**
     * Get all approved ratings for a vehicle
     */
    public function index($vehicleId)
    {
        $ratings = Rating::where('vehicle_id', $vehicleId)
                        ->where('status', 'approved')
                        ->get();

        return response()->json(['data' => $ratings]);
    }


    /**
     * Submit a new rating
     */
    public function store(Request $request) {
        // Validate required fields
        $validated = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'vehicle_id'     => 'required|exists:vehicles,id',
            'reservation_id' => 'required|exists:reservations,id',
            'rating'         => 'required|integer|min:1|max:5',
            'feedback'       => 'nullable|string|max:500',
        ]);

        // Create the rating safely, setting optional fields to null if not provided
        $rating = Rating::create([
            'customer_id'   => $validated['customer_id'],
            'vehicle_id'    => $validated['vehicle_id'],
            'reservation_id'=> $validated['reservation_id'],
            'admin_id'      => $request->input('admin_id', null),     // optional
            'rating'        => $validated['rating'],
            'feedback'      => $validated['feedback'] ? strip_tags($validated['feedback']) : null,
            'adminreply'    => $request->input('adminreply', null),   // optional
            'status'        => $request->input('status', 'pending'),  // default pending
        ]);

        return response()->json([
            'message' => 'Rating submitted successfully',
            'data'    => $rating
        ], 201);
    }
    /**
     * Delete a rating by ID
     */
    public function destroy($id) {
        $rating = Rating::find($id);
        if (!$rating) {
            return response()->json(['error' => 'Rating not found'], 404);
        }

        $rating->delete();

        return response()->json([
            'message' => 'Rating deleted successfully'
        ]);
    }
    //update
    public function update(Request $request, $id)
    {
        $rating = Rating::find($id);
        if (!$rating) {
            return response()->json(['error' => 'Rating not found'], 404);
        }

        $validated = $request->validate([
            'rating'   => 'sometimes|integer|min:1|max:5',
            'feedback' => 'sometimes|string|max:500',
            'status'   => 'sometimes|string|in:pending,approved,rejected',
        ]);

        $rating->update($validated);

        return response()->json([
            'message' => 'Rating updated successfully',
            'data' => $rating
        ]);
    }

    /**
     * Get average rating of a vehicle
     */
    // public function rating($vehicleId) {
    //     $vehicle = Vehicle::findOrFail($vehicleId);
    //     $response = [
    //         'vehicle_id'     => $vehicleId,
    //         'average_rating' => $vehicle->average_rating,
    //     ];
    //     return response()->json(['data' => $response]);
    // }
    public function rating($vehicleId) {
        $avg = $this->ratingService->getAverageRating($vehicleId);
        return response()->json(['data' => ['vehicle_id' => $vehicleId, 'average_rating' => $avg]]);
}
}
