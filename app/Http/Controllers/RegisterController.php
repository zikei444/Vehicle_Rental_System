<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Factories\UserFactory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('user.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'phone'    => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Create user using Factory (role: customer)
        $user = UserFactory::create('customer', [
            'name' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->save();

        // Store phone number in customer table
        $user->customer()->create([
            'phoneNo' => $validated['phone'],
        ]);

        return redirect()->route('login')->with('success', 'Registration successful. Please log in.');
    }
}
