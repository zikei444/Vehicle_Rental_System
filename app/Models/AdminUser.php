<?php

namespace App\Models;

use App\Models\User;
use App\Models\FAQ;
use App\Models\Vehicle;

class AdminUser extends User
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->role = 'admin';
    }

}