<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


// Customer Change Their Own
class ProfileController extends Controller
{
    private $userApi;

    public function __construct()
    {
        $this->userApi = 'http://127.0.0.1/Vehicle_Rental_System/public/api/userApi.php';
    }

    public function edit()
    {
        $user = session('user');

        if (!$user || !$user->id) {
            return redirect()->route('login')->withErrors('You must log in first.');
        }

        $response = Http::get($this->userApi . "?action=get&id=" . $user->id);

        if ($response->successful() && $response->json('status') === 'success') {
            $userData = (object) $response->json('data');
            return view('user.profile', compact('userData'));
        }

        return view('user.profile', ['userData' => $user]);
    }


    public function update(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
        ]);

        $user = session('user');

        if (!$user || !$user->id) {
            return redirect()->route('login')->withErrors('You must log in first.');
        }

        $response = Http::asForm()->put($this->userApi . '?action=update&id=' . $user->id, [
            'username' => $request->username,
            'phone'    => $request->phone,
        ]);

        if ($response->successful() && $response->json('status') === 'success') {
            $user->name = $request->username;
            $user->phone = $request->phone;
            session(['user' => $user]);

            return redirect()->route('profile.edit')->with('success', 'Profile updated successfully!');
        }

        return back()->withErrors('Failed to update profile.');
    }

}
