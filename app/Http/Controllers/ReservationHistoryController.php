<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class ReservationHistoryController extends Controller
{
    public function index()
    {
        // Get reservations for the logged-in customer
        $customer = Auth::user()->customer;
        $reservations = Reservation::with('vehicle', 'rating')
            ->where('customer_id', $customer->id)
            ->orderBy('pickup_date', 'desc')
            ->get();

        // Load the new Blade view under reservations folder
        return view('reservations.history', compact('reservations'));
    }
}
