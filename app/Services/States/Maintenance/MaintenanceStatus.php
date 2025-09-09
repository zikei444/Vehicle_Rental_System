<?php

namespace App\Services\Maintenance;

use App\Models\Maintenance;

interface MaintenanceStatus
{
    public function name(): string;

    /**
     * Validate + perform transition, update fields, and save.
     */
    public function transitionTo(Maintenance $m, string $newStatus): Maintenance;
}