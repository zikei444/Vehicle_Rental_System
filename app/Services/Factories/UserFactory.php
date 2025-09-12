<?php

// STUDENT NAME: WONG XINN
// STUDENT ID: 23WMR14632

namespace App\Services\Factories;

use App\Models\AdminUser;
use App\Models\CustomerUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserFactory
{
    public static function create($role, $data)
    {
        if ($role === 'admin') {
            $user = new AdminUser();
        } else {
            $user = new CustomerUser();
        }

        $user->name = $data['name'] ?? null;
        $user->email = $data['email'] ?? null;
        $user->role = $data['role'] ?? $role;
        $user->password = isset($data['password']) ? Hash::make($data['password']) : null;

        return $user;
    }

        public static function deleteUser(User $user)
    {
        // If the user has related customer/reservations, delete them first
        if ($user->customer) {
            $user->customer->reservations()->delete(); // delete related reservations
            $user->customer->delete(); // delete customer profile
        }

        // Delete user account
        $user->delete();
    }
}

