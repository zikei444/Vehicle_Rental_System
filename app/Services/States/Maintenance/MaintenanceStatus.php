<?php

// STUDENT NAME: Loh Yun Le
// STUDENT ID: 23WMR14583

namespace App\Services\States\Maintenance;

use App\Models\Maintenance;

interface MaintenanceStatus
{
    public function name(): string;
    // Check and apply the status change, update fields, then save
    public function transitionTo(Maintenance $m, string $newStatus): Maintenance;
}