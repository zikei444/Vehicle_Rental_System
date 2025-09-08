<?php

namespace App\States\Maintenance;

use App\Models\Maintenance;

class Completed extends BaseStatus
{
    public function name(): string { return 'completed'; }

    public function transitionTo(Maintenance $m, string $newStatus): Maintenance
    {
        // usually terminal
        return $this->fail("Completed is terminal. No further transitions.");
    }
}
