<?php

namespace App\Services\Maintenance;

use App\Models\Maintenance;

class Completed extends BaseStatus
{
    public function name(): string { return 'Completed'; }
    /**
     * Once a maintenance record is completed, it cannot change status anymore.
     */
    public function transitionTo(Maintenance $m, string $newStatus): Maintenance {
        return $this->fail("Completed is terminal. No further transitions allowed.");
    }
}
