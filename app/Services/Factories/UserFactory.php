<?php

namespace App\Services\Factories;

use App\Models\AdminUser;
use App\Models\CustomerUser;

class UserFactory
{
    public static function create($role, $data)
    {
        if ($role === 'admin') {
            $user = new AdminUser();
        } else {
            $user = new CustomerUser(); // now points to users table
        }

        $user->name = $data['name'] ?? null;
        $user->email = $data['email'] ?? null;
        $user->role = $data['role'] ?? $role;
        $user->password = $data['password'] ?? null;

        return $user;
    }
}

