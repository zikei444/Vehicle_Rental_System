<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;

use App\Models\Rating;
use App\Models\Comment;
use App\Observers\RatingObserver;
use App\Observers\CommentObserver;

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
        Comment::observe(CommentObserver::class);
    }
}
