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
            $user = new CustomerUser();
        }

        // Assign API data to the model
        $user->id = $data['id'] ?? null;
        $user->name = $data['name'] ?? null;
        $user->email = $data['email'] ?? null;
        $user->role = $data['role'] ?? null;
        $user->phone = $data['phone'] ?? null;

        return $user;
    }
}