<?php

namespace App\Services\Factories;

use App\Models\AdminUser;
use App\Models\CustomerUser;
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
}

