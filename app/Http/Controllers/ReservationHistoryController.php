<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class ReservationHistoryController extends Controller
{
    public function index()
    {
        // Fetch the customer record linked to the logged-in user
        $customer = Customer::where('user_id', Auth::id())->first();

        if (!$customer) {
            return redirect()->back()->with('error', 'No customer profile found.');
        }

        // Get reservations for this customer
        $reservations = Reservation::with('vehicle', 'rating')
            ->where('customer_id', $customer->id)
            ->orderBy('pickup_date', 'desc')
            ->get();

        return view('reservations.history', compact('reservations'));
    }
}
