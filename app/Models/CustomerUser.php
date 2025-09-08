<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerUser extends Authenticatable
{
    use HasFactory;

    protected $table = 'users'; // <--- important

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    public function customer()
    {
        return $this->hasOne(Customer::class, 'user_id');
    }
}
