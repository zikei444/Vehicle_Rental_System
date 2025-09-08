<?php

namespace App\States\Maintenance;

use App\Models\Maintenance;

class Scheduled extends BaseStatus
{
    public function name(): string { return 'scheduled'; }

    public function transitionTo(Maintenance $m, string $newStatus): Maintenance
    {
        return match ($newStatus) {
            'in_progress' => $this->setAndSave($m, 'in_progress'),
            'cancelled'   => $this->setAndSave($m, 'cancelled'),
            default       => $this->fail("From scheduled you can only go to in_progress or cancelled."),
        };
    }
}
