<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Factories\UserFactory;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    private $userApi = 'http://127.0.0.1/Vehicle_Rental_System/public/api/userApi.php';

    // Display Login form
    public function showLoginForm()
    {
        return view('user.login');
    }

    // Handle Login Request
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $response = Http::asForm()->post($this->userApi . '?action=login', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if (!$response->successful() || $response->json('status') !== 'success') {
            return back()->withErrors([
                'email' => $response->json('message') ?? 'Login failed'
            ])->withInput();
        }

        $userData = $response->json('data');

        // Create object using factory
        $user = UserFactory::create($userData['role'], $userData);

        // Store object in session
        session(['user' => $user]);

        // Redirect to dashboard based on role
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Login successful!');
        } else {
            return redirect()->route('customer.dashboard')->with('success', 'Login successful!');
        }
    }

    // Loutout
    public function logout(Request $request)
    {
        $request->session()->forget('user');
        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}
