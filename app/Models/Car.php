<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $table = 'cars';

    protected $fillable = [
        'vehicle_id',
        'fuel_type',
        'transmission',
        'seats',
        'air_conditioning', 
        'fuel_efficiency',
    ];

    // Relation back to Vehicle
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}

?>