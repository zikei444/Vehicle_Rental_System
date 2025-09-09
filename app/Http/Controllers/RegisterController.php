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
        $validated = $request->validate(
            [
                'username' => 'required|string|max:30',
                'email'    => 'required|string|email|max:255|unique:users',
                'phone'    => 'required|string|max:11|min:10',
                'password' => 'required|string|min:6|confirmed',
            ],
            [
                'username.required' => 'Username is required.',
                'username.max' => 'Username must not exceed 30 characters.',
                'email.required' => 'Email is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email is already registered.',
                'phone.required' => 'Phone number is required.',
                'phone.max' => 'Phone number must not be more than 11 digits.',
                'phone.min' => 'Phone number must be at least 10 digits.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 6 characters.',
                'password.confirmed' => 'Password confirmation does not match.',
            ]
        );

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
