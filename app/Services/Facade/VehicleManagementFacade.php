<?php

// STUDENT NAME: Lian Wei Ying
// STUDENT ID: 23WMR14568

namespace App\Services\Facade;

use Illuminate\Support\Facades\Facade;

class VehicleManagementFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'vehicle.management'; // Binding in AppServiceProvider
    }
}