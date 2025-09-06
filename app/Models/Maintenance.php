<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $table = 'maintenance';

    // keep fillable in sync with table
    protected $fillable = [
        'vehicle_id','admin_id','maintenance_type','service_date','cost','notes','status','completed_at'
    ];

    protected $casts = [
        'service_date' => 'date',
        'completed_at' => 'datetime',
        'cost' => 'decimal:2',
    ];

    // tiny enum using constants to avoid typos
    public const SCHEDULED = 'Scheduled';
    public const COMPLETED = 'Completed';
    public const CANCELLED = 'Cancelled';

    public function vehicle(){ return $this->belongsTo(Vehicle::class); }
    public function admin(){ return $this->belongsTo(Admin::class); } // change if your admin model differs
}
