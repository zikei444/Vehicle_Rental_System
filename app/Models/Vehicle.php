<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
use App\Models\Reservation;  
use App\Models\Car;
use App\Models\Truck;
use App\Models\Van;
=======

use App\Models\Maintenance;
use App\States\Vehicle\AvailableState;
use App\States\Vehicle\UnderMaintenanceState;
>>>>>>> 4237f6c7827c954e409a66df53c3acf6267c0be0

class Vehicle extends Model
{
    protected $table = 'vehicles';

    protected $fillable = [
        'type',
        'brand',
        'model',
<<<<<<< HEAD
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
=======
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
>>>>>>> 4237f6c7827c954e409a66df53c3acf6267c0be0
