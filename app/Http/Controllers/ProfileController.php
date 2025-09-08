<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ProfileController extends Controller
{
    // Show the profile edit form
    public function edit()
    {
        $user = auth()->user()->load('customer'); // eager load customer relation

        if (!$user) {
            return redirect()->route('login')->withErrors('You must log in first.');
        }

        return view('user.profile', [
            'userData' => [
                'name'    => $user->name,
                'email'   => $user->email,
                'phone'   => $user->customer->phoneNo ?? '', // use customer relation
            ]
        ]);
    }


    // Handle profile update
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
