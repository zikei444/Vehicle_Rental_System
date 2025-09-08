<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\States\Maintenance\MaintenanceStatus;
use App\States\Maintenance\Scheduled;
use App\States\Maintenance\Completed;
use App\States\Maintenance\Cancelled;


class Maintenance extends Model {
    protected $fillable = [
        'vehicle_id', 'admin_id', 'maintenance_type', 'status', 'service_date', 'completed_at', 'cost', 'notes'
    ];

    public function vehicle() {
        return $this->belongsTo(\App\Models\Vehicle::class, 'vehicle_id');
    }

    public function admin() {
        return $this->belongsTo(\App\Models\Admin::class, 'admin_id');
    }

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