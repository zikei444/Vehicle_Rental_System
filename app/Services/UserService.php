<?php

namespace App\Services;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class UserService
{
    /**
     * Return all users in JSON
     */
    public function all(): JsonResponse
    {
        $users = User::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($u) => $this->formatUser($u));

        return response()->json(['data' => $users]);
    }

    /**
     * Return single user by ID as JSON
     */
    public function find(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json(['data' => $this->formatUser($user)]);
    }

    /**
     * Create a user and return as JSON
     */
    public function create(array $data): JsonResponse
    {
        // Password hashing for security
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user = User::create($data);
        return response()->json(['data' => $this->formatUser($user)], 201);
    }

    /**
     * Update user by ID and return as JSON
     */
    public function update(int $id, array $data): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Password hashing if provided
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);
        return response()->json(['data' => $this->formatUser($user)]);
    }

    /**
     * Delete user by ID and return JSON
     */
    public function delete(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['status' => 'success', 'message' => 'User deleted']);
    }

    /**
     * Format user for JSON response
     */
    private function formatUser(User $u): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'created_at' => $u->created_at?->toDateTimeString(),
            'updated_at' => $u->updated_at?->toDateTimeString(),
        ];
    }

    public function getCustomerIdByUserId(int $userId): JsonResponse
    {
        $customer = Customer::where('user_id', $userId)->first();

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'customer_id' => $customer->id
        ]);
    }
}
