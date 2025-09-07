<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    protected $table = 'trucks';

    protected $fillable = [
        'vehicle_id',
        'truck_type',
        'load_capacity',
        'fuel_type',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
