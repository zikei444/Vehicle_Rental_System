<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Maintenance\MaintenanceStatus;
use App\Services\Maintenance\Scheduled;
use App\Services\Maintenance\Completed;
use App\Services\Maintenance\Cancelled;


class Maintenance extends Model {
    protected $fillable = [
        'vehicle_id', 'maintenance_type', 'status', 'service_date', 'completed_at', 'cost', 'notes'
    ];

    public function vehicle() {
        return $this->belongsTo(\App\Models\Vehicle::class, 'vehicle_id');
    }

    protected $casts = [
        'service_date' => 'date',
        'completed_at' => 'datetime',
    ];

    // Map status string to state class
    public function state(): MaintenanceStatus {
        return match ($this->status) {
            'Scheduled' => new Scheduled(),
            'Completed' => new Completed(),
            'Cancelled' => new Cancelled(),
            default     => new Scheduled(),
        };
    }

    /**
     * Safe transition using the State pattern.
     */
    public function transitionTo(string $newStatus): self {
        return $this->state()->transitionTo($this, $newStatus);
    }
}