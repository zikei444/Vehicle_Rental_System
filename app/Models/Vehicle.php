<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $table = 'vehicles';

    protected $fillable = [
        'type',
        'brand',
        'model',
        'registration_number',
        'rental_price',
        'availability_status'
    ];
}
