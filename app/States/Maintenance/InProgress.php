<?php

namespace App\States\Maintenance;

use App\Models\Maintenance;

class InProgress extends BaseStatus
{
    public function name(): string { return 'in_progress'; }

    public function transitionTo(Maintenance $m, string $newStatus): Maintenance
    {
        return match ($newStatus) {
            'completed' => $this->setAndSave($m, 'completed'),
            'cancelled' => $this->setAndSave($m, 'cancelled'),
            default     => $this->fail("From in_progress you can only go to completed or cancelled."),
        };
    }
}
