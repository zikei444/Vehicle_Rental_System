<?php

namespace App\Http\Controllers;

use App\Models\Reservation;

class RatingController extends Controller
{
    public function create($rentalId)
    {
        $rental = Reservation::findOrFail($rentalId);
        return view('ratings.create', compact('rental'));
    }
}
