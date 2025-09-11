<?php

namespace App\Services\States\Maintenance;

use App\Models\Maintenance;

class Cancelled extends BaseStatus
{
    public function name(): string { return 'Cancelled'; }
    // Once  maintenance record is cancelled, it cannot change anymore. 
    public function transitionTo(Maintenance $m, string $newStatus): Maintenance {
        return $this->fail("This maintenance is cancelled. You cannot change anymore.");
    }
}
