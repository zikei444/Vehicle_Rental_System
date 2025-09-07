<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Van extends Model
{
    protected $table = 'vans';

    protected $fillable = [
        'vehicle_id',
        'passenger_capacity',
        'fuel_type',
        'air_conditioning', 
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}

?>