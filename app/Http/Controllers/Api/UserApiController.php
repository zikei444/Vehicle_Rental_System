<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserApiController extends Controller
{
    // List all customer
    public function index()
    {
        $customers = User::where('role', 'customer')
            ->with('customer')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $customers
        ]);
    }

    //Show a single customer
    public function show($id)
    {
        $user = User::with('customer')->find($id);

        if (!$user || $user->role !== 'customer') {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    //Create a new customer    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:30',
            'email'    => 'required|string|email|max:255|unique:users',
            'phone'    => 'required|string|max:11|min:10',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
        ]);

        $customer = Customer::create([
            'user_id' => $user->id,
            'phoneNo' => $validated['phone'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully',
            'data' => [
                'user' => $user,
                'customer' => $customer
            ]
        ], 201);
    }

    //Update customer info
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:30',
            'phone'    => 'required|string|max:11|min:10',
        ]);

        $user = User::findOrFail($id);

        if ($user->role !== 'customer') {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid user type'
            ], 400);
        }

        $user->update(['name' => $validated['username']]);
        $user->customer->update(['phoneNo' => $validated['phone']]);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer updated successfully',
            'data' => $user->load('customer')
        ]);
    }

    /**
     * Delete customer
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'customer') {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid user type'
            ], 400);
        }

        $user->customer()->delete();
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer deleted successfully'
        ]);
    }
}
