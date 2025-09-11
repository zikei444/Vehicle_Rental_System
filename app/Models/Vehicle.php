<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Reservation;  
use App\Models\Car;
use App\Models\Truck;
use App\Models\Van;

use App\Models\Maintenance;
use App\Services\States\Vehicle\AvailableState;
use App\Services\States\Vehicle\VehicleState;
use App\Services\States\Vehicle\UnderMaintenanceState;

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
        'insurance_doc',       
        'registration_doc',    
        'roadtax_doc',  
    ];

    // use constants to avoid typos everywhere
    public const AVAILABLE          = 'available';
    public const RESERVED           = 'reserved';
    public const RENTED             = 'rented';
    public const UNDER_MAINTENANCE  = 'under_maintenance';

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
        return $this->hasOne(Car::class);
    }

    public function truck()
    {
        return $this->hasOne(Truck::class);
    }

    public function van()
    {
        return $this->hasOne(Van::class);
    }

    // Scope to filter available vehicles
    public function scopeAvailable($query)
    {
        return $query->where('availability_status', self::AVAILABLE);
    }

    // Scope to filter by type (car/truck/van)
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Relationship: one vehicle can have many maintenance records
    public function maintenanceRecords()
    { 
        return $this->hasMany(Maintenance::class);
    }

    // State Pattern
    private ?VehicleState $state = null;

    public function setState(VehicleState $state): void {
        $this->state = $state;
    }

    public function getState(): VehicleState {
        // If no state object is set yet, create one based on the current DB value.
        // - If availability_status is "under_maintenance", wrap the Vehicle in an UnderMaintenanceState.
        // - Otherwise (available, reserved, rented, etc.), default to AvailableState.
        if (!$this->state) {
            $this->state = $this->availability_status === self::UNDER_MAINTENANCE
                ? new UnderMaintenanceState($this)
                : new AvailableState($this);
        }
        return $this->state;
    }
}
