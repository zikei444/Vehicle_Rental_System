<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Rating;
use App\Models\RatingLog;
use App\Notifications\RatingReplied;
use App\Notifications\NewRatingCreated;

class RatingObserver
{

    // public function boot()
    // {
    //     Rating::observe(RatingObserver::class);
    // }

    // When a new rating is created → notify admins
    public function created(Rating $rating)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $exists = $admin->notifications()
                ->where('type', 'App\Notifications\NewRatingCreated')
                ->whereJsonContains('data->rating_id', $rating->id)
                ->exists();

            if (!$exists) {
                $admin->notify(new NewRatingCreated($rating));
            }
        }
    }

    // When rating is updated → notify customer if admin replied
    public function updated(Rating $rating)
{
    // Only trigger if admin replied and reply is not empty
    if ($rating->wasChanged('adminreply') && $rating->adminreply) {

        // Save to rating log
        RatingLog::create([
            'rating_id' => $rating->id,
            'customer_id' => $rating->customer_id,
            'reply' => $rating->adminreply,
        ]);

        // Notify customer safely
        $customer = $rating->customer;

        if ($customer) {
            $user = $customer->user;

            // Only send notification if user exists and hasn't received one for this reply yet
            if ($user && !$user->notifications()
                ->where('type', RatingReplied::class)
                ->whereJsonContains('data->rating_id', $rating->id)
                ->exists()
            ) {
                $user->notify(new RatingReplied($rating, $rating->adminreply));
            }
        }
    }
}
}
