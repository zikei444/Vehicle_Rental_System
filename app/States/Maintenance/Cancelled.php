<?php

namespace App\States\Maintenance;

use App\Models\Maintenance;

class Cancelled extends BaseStatus
{
    public function name(): string { return 'cancelled'; }

    public function transitionTo(Maintenance $m, string $newStatus): Maintenance
    {
        // usually terminal, or allow reschedule if you want
        return $this->fail("Cancelled is terminal. No further transitions.");
    }
}
