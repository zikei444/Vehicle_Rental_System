<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'rental_id' => 'required|exists:reservations,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        // Check if reservation belongs to user and is completed
        $reservation = Reservation::with('rating')
            ->where('id', $data['rental_id'])
            ->where('customer_id', Auth::user()->customer->id)
            ->first();

        if (!$reservation) {
            return response()->json(['status'=>'error','message'=>'Reservation not found or not yours.']);
        }

        if ($reservation->status !== 'completed') {
            return response()->json(['status'=>'error','message'=>'Cannot rate a reservation that is not completed.']);
        }

        if ($reservation->rating) {
            return response()->json(['status'=>'error','message'=>'You have already rated this reservation.']);
        }

        // Create rating
        $rating = Rating::create([
            'customer_id' => Auth::user()->customer->id,
            'vehicle_id' => $data['vehicle_id'],
            'admin_id' => null, // can be assigned later if needed
            'rating' => $data['rating'],
            'feedback' => $data['comment'],
        ]);

        // Observer will automatically handle thank-you email or notifications

        return response()->json(['status'=>'success','message'=>'Thank you for your rating!']);
    }
}
