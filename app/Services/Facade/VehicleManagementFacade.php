<?php

namespace App\Services\Facade;

use Illuminate\Support\Facades\Facade;

class VehicleManagementFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'vehicle.management'; // Binding in AppServiceProvider
    }
}