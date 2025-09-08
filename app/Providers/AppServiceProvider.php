<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;

use App\Models\Rating;
use App\Observers\RatingObserver;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public function boot(): void
    {
        Rating::observe(RatingObserver::class);
        Paginator::useBootstrapFive();
    }
}
