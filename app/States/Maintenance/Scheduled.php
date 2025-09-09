<?php

namespace App\States\Maintenance;

use App\Models\Maintenance;

class Scheduled extends BaseStatus {
    public function name(): string { return 'Scheduled'; }
    /**
     * Allowed change the schedule to Completed / Cancelled
     */
    public function transitionTo(Maintenance $m, string $newStatus): Maintenance {
        return match ($newStatus) {
            'Completed' => $this->setAndSave($m, 'Completed'),
            'Cancelled' => $this->setAndSave($m, 'Cancelled'),
            default     => $this->fail("From Scheduled you can only go to Completed or Cancelled."),
        };
    }
}
