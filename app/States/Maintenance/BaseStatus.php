<?php

namespace App\States\Maintenance;

use App\Models\Maintenance;
use Illuminate\Validation\ValidationException;

abstract class BaseStatus implements MaintenanceStatus
{
    abstract public function name(): string;

    protected function fail(string $message): never
    {
        throw ValidationException::withMessages(['status' => $message]);
    }

    protected function setAndSave(Maintenance $m, string $status): Maintenance
    {
        $m->status = $status;
        if ($status === 'Completed' && is_null($m->completed_at)) {
            $m->completed_at = now();
        }
        $m->save();
        return $m;
    }

    public function transitionTo(Maintenance $m, string $newStatus): Maintenance
    {
        $this->fail("Transition from {$this->name()} to {$newStatus} is not allowed.");
    }
}