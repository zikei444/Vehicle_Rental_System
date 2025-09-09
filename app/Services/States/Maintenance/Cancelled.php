<?php

namespace App\Services\Maintenance;

use App\Models\Maintenance;

class Cancelled extends BaseStatus
{
    public function name(): string { return 'Cancelled'; }
    /**
     * Once a maintenance record is cancelled, it cannot change status anymore.
     */

    public function transitionTo(Maintenance $m, string $newStatus): Maintenance {
        return $this->fail("Cancelled is terminal. No further transitions allowed.");
    }
}
