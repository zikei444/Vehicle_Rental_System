<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Reservation;  
use App\Models\Car;
use App\Models\Truck;
use App\Models\Van;

class Vehicle extends Model
{
    protected $table = 'vehicles';

    protected $fillable = [
        'type',
        'brand',
        'model',
        'year_of_manufacture', 
        'registration_number',
        'rental_price',
        'availability_status',
        'image',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function car()
    {
        return $this->hasOne(\App\Models\Car::class);
    }

    public function truck()
    {
        return $this->hasOne(\App\Models\Truck::class);
    }

    public function van()
    {
        return $this->hasOne(\App\Models\Van::class);
    }

    // Scope to filter available vehicles
    public function scopeAvailable($query)
    {
        return $query->where('availability_status', 'available');
    }

    // Scope to filter by type (car/truck/van)
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}

?>