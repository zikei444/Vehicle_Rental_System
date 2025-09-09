<?php

namespace App\Models;

use App\Models\User;

class AdminUser extends User
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->role = 'admin';
    }

    public function maintenances()
    {
        return $this->hasMany(\App\Models\Maintenance::class, 'admin_id');
    }


}