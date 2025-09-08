<?php

namespace App\Models;

class CustomerUser extends User
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->role = 'customer';
    }
}