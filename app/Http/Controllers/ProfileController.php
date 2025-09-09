<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReservationService;

class ProfileController extends Controller
{
    private $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    // Show the profile edit form 
    public function edit()
    {
        $user = auth()->user()->load('customer');

        if (!$user) {
            return redirect()->route('login')->withErrors('You must log in first.');
        }

        // Call API from reservation service
        $reservationsJson = $this->reservationService->allByCustomer($user->id);
        $reservations = collect($reservationsJson->getData(true)['data']);

        // Count by status
        $reservationSummary = [
            'ongoing'   => $reservations->where('status', 'ongoing')->count(),
            'cancelled' => $reservations->where('status', 'cancelled')->count(),
            'completed' => $reservations->where('status', 'completed')->count(),
        ];

        return view('user.profile', [
            'userData' => [
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->customer->phoneNo ?? '',
            ],
            'reservationSummary' => $reservationSummary
        ]);
    }

    // Update profile
    public function update(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
        ]);

        $user = auth()->user()->load('customer');

        if (!$user) {
            return redirect()->route('login')->withErrors('You must log in first.');
        }

        $user->name = $validated['username'];
        $user->save();

        if ($user->customer) {
            $user->customer->update(['phoneNo' => $validated['phone']]);
        }

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully!');
    }
}
