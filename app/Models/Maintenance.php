<?php

// STUDENT NAME: Loh Yun Le
// STUDENT ID: 23WMR14583

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\States\Maintenance\MaintenanceStatus;
use App\Services\States\Maintenance\Scheduled;
use App\Services\States\Maintenance\Completed;
use App\Services\States\Maintenance\Cancelled;


class Maintenance extends Model {
    protected $fillable = [
        'vehicle_id', 'maintenance_type', 'status', 'service_date', 'completed_at', 'cost', 'notes'
    ];

    public function vehicle() {
        return $this->belongsTo(\App\Models\Vehicle::class, 'vehicle_id');
    }

    // Cast dates to proper types
    protected $casts = [
        'service_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function state(): MaintenanceStatus {
        return match ($this->status) {
            'Scheduled' => new Scheduled(),
            'Completed' => new Completed(),
            'Cancelled' => new Cancelled(),
            default     => new Scheduled(),
        };
    }

    // Change status
    public function transitionTo(string $newStatus): self {
        return $this->state()->transitionTo($this, $newStatus);
    }
}