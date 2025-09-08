<?php

namespace App\Services\Factories;

use App\Models\User;

class UserFactory
{
    public static function create(string $role, array $data): User
    {
        return new User([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => bcrypt($data['password']),
            'role' => 'customer', // Only customer can register
        ]);
    }
}
