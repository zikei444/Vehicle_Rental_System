<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\States\Maintenance\MaintenanceStatus;
use App\States\Maintenance\Scheduled;
use App\States\Maintenance\InProgress;
use App\States\Maintenance\Completed;
use App\States\Maintenance\Cancelled;


class Maintenance extends Model
{
    protected $fillable = [
        'vehicle_id', 'type', 'status', 'service_date', 'completed_at', 'cost', 'notes'
    ];

    // Map status string to state class
    public function state(): MaintenanceStatus
    {
        return match ($this->status) {
            'scheduled'   => new Scheduled(),
            'in_progress' => new InProgress(),
            'completed'   => new Completed(),
            'cancelled'   => new Cancelled(),
            default       => new Scheduled(), // default fallback
        };
    }

    /**
     * Safe transition using the State pattern.
     */
    public function transitionTo(string $newStatus): self
    {
        return $this->state()->transitionTo($this, $newStatus);
    }
}