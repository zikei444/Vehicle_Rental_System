<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $table = 'maintenance';

    protected $fillable = [
        'vehicle_id','admin_id','maintenance_type','service_date',
        'cost','notes','status','completed_at',
    ];

    protected $casts = [
        'service_date' => 'date',
        'completed_at' => 'datetime',
        'cost'         => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}