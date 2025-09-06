<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Maintenance;
use App\States\Vehicle\AvailableState;
use App\States\Vehicle\UnderMaintenanceState;

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

     // use constants to avoid typos everywhere
    public const AVAILABLE          = 'available';
    public const RESERVED           = 'reserved';
    public const RENTED             = 'rented';
    public const UNDER_MAINTENANCE  = 'under_maintenance';

    // Relationship: one vehicle can have many maintenance records
    public function maintenanceRecords()
    { 
        return $this->hasMany(Maintenance::class);
    }

    // Scope: get only available vehicles
    public function scopeAvailable($q)
    {
        return $q->where('availability_status', 'Available');       // use constant so string is not mistyped
    }

    // State Pattern
    private $state;

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        if (!$this->state) {
            // map DB value to a State object
            if ($this->availability_status === self::UNDER_MAINTENANCE) {
                $this->state = new UnderMaintenanceState($this);
            } else {
                // default to AvailableState for any other value
                $this->state = new AvailableState($this);
            }
        }
        return $this->state;
    }
}