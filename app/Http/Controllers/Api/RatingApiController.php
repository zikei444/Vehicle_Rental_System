<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\Reservation;

class RatingApiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'rental_id' => 'required|exists:reservations,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $reservation = Reservation::find($request->rental_id);
        if ($reservation->status !== 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only rate after completing the rental.'
            ], 400);
        }

        $rating = Rating::create([
            'rental_id' => $request->rental_id,
            'vehicle_id' => $request->vehicle_id,
            'user_id' => $request->user_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'approved' => 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Thank you for your feedback! Your rating is pending admin approval.'
        ]);
    }

    public function approve($id)
    {
        $rating = Rating::findOrFail($id);
        $rating->approved = 1;
        $rating->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Rating approved successfully.'
        ]);
    }

    public function index()
    {
        $ratings = Rating::where('approved', 1)->get();
        return response()->json(['status'=>'success','data'=>$ratings]);
    }
}
