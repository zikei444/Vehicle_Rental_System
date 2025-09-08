<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    private $userApi = 'http://127.0.0.1/Vehicle_Rental_System/public/api/userApi.php'; // API path

    // Display registration form
    public function showRegisterForm()
    {
        return view('user.register');
    }

    // Validate & Save the form
    public function register(Request $request)
    {
        // Validate
        $request->validate([
            'username' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|min:10',
            'password' => 'required|confirmed|min:6',
        ]);

        // Send data to API
        $response = Http::asForm()->post($this->userApi . '?action=register', [
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
        ]);

        // Get new user ID from API response
        $newUserId = $response->json('data.id');
        
        // 
        if ($response->successful() && $response->json('status') === 'success') {
            return redirect()->route('login');
        }

        // Error message
        return back()->withErrors(['error' => $response->json('message') ?? 'Registration failed'])->withInput();
    }
}