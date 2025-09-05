<?php
namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function create($reservationId)
    {
        $reservation = Reservation::completed()
            ->where('id', $reservationId)
            ->where('customer_id', auth()->user()->customer->id)
            ->firstOrFail();

        return view('ratings.create', compact('reservation'));
    }

    public function store(Request $request, $reservationId)
    {
        $reservation = Reservation::with('rating')
            ->completed()
            ->where('id', $reservationId)
            ->where('customer_id', auth()->user()->customer->id)
            ->firstOrFail();

        if ($reservation->rating) {
            return redirect()->back()->with('error', 'You already submitted feedback for this rental.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string'
        ]);

        Rating::create([
            'reservation_id' => $reservation->id,
            'customer_id' => $reservation->customer_id,
            'vehicle_id' => $reservation->vehicle_id,
            'rating' => $validated['rating'],
            'feedback' => $validated['feedback'] ?? null,
        ]);

        return redirect()->route('reservations.index')->with('success', 'Thank you for your feedback!');
    }
    public function index()
        {
            // only fetch approved ratings
            $ratings = \App\Models\Rating::approved()->get();

            return view('feedback.index', compact('ratings'));
        }
     public function manage()
        {
            $ratings = Rating::all(); // admin sees ALL feedback
            return view('feedback.manage', compact('ratings'));
        }

    /**
     * Update feedback status (approve/reject)
     */
    public function updateStatus(Request $request, $id)
        {
            $rating = Rating::findOrFail($id);
            $rating->status = $request->input('status'); // 'approved' or 'rejected'
            $rating->save();

            return redirect()->route('feedback.manage')
                ->with('success', 'Feedback status updated successfully!');
        }
}

