<?php

// STUDENT NAME: Loh Yun Le
// STUDENT ID: 23WMR14583

namespace App\Services\States\Maintenance;

use App\Models\Maintenance;

class Completed extends BaseStatus
{
    public function name(): string { return 'Completed'; }
    // Once maintenance record is completed, it cannot change anymore.
    public function transitionTo(Maintenance $m, string $newStatus): Maintenance {
        return $this->fail("This maintenance is completed. You cannot change anymore.");
    }
}
