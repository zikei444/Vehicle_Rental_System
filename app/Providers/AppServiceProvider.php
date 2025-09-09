<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Rating;
use App\Observers\RatingObserver;
use Illuminate\Pagination\Paginator;

use App\Services\VehicleManagementService;
use App\Services\VehicleService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // Bind VehicleManagementService for the Facade
        $this->app->singleton('vehicle.management', function ($app) {
            return new VehicleManagementService($app->make(VehicleService::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Rating::observe(RatingObserver::class);
        Paginator::useBootstrapFive();
    }
}
