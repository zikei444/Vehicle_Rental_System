<?php

namespace App\Providers;
use App\Models\Rating;
use Illuminate\Support\ServiceProvider;
use App\Observers\RatingObserver;
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
    }
}
