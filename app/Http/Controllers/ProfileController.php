<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Http;
use App\Services\Factories\UserFactory;

class ProfileController extends Controller
{
    private $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    // Show the profile edit form 
    public function edit(Request $request)
    {
        $user = auth()->user()->load('customer');

        $customerId = $user->customer->id ?? null;

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['auth' => 'You must log in to view your profile.']);
        }

        $useApi = $request->query('use_api', false);

        if ($useApi) {
            // 🔹 API call
            $response = Http::get(url("/api/customers/{$customerId}/reservations"));

            if ($response->failed()) {
                return redirect()->back()->withErrors(['reservation' => 'Failed to fetch reservations from API.']);
            }

            $reservations = collect($response->json('data') ?? []);
        } else {
            // 🔹 Internal service
            $serviceResponse = $this->reservationService->allByCustomer($customerId);

            $reservations = $serviceResponse instanceof \Illuminate\Http\JsonResponse
                ? collect($serviceResponse->getData(true)['data'] ?? [])
                : collect($serviceResponse['data'] ?? []);
        }

        // Count by status
        $reservationSummary = [
            'ongoing' => $reservations->where('status', 'ongoing')->count(),
            'cancelled' => $reservations->where('status', 'cancelled')->count(),
            'completed' => $reservations->where('status', 'completed')->count(),
        ];

        return view('user.profile', [
            'userData' => [
                'name' => $user->name,
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
            'username' => 'required|string|max:30',
            'phone' => 'required|string|max:11|min:10',
        ], [
            'username.required' => 'Username is required.',
            'username.max' => 'Username cannot exceed 30 characters.',
            'phone.required' => 'Phone number is required.',
            'phone.max' => 'Phone number must not be more than 11 digits.',
            'phone.min' => 'Phone number must be at least 10 digits'
        ]);

        $user = auth()->user()->load('customer');


        $user->name = $validated['username'];
        $user->save();

        if ($user->customer) {
            $user->customer->update(['phoneNo' => $validated['phone']]);
        } else {
            return redirect()->route('profile.edit')
                ->withErrors(['customer' => 'Your customer details could not be found.']);
        }

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully!');
    }

    // Delete account
    public function destroy()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')->withErrors(['auth' => 'You must log in to delete your account.']);
        }

        UserFactory::deleteUser($user);

        auth()->logout();

        return redirect()->route('register')->with('success', 'Your account has been deleted successfully!');
    }
}
