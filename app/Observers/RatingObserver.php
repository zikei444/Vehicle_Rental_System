<?php

namespace App\Observers;

use App\Models\Rating;

class RatingObserver
{
    /**
     * Handle the Rating "created" event.
     *
     * @param  \App\Models\Rating  $rating
     * @return void
     */
    public function created(Rating $rating)
    {
                // Example: Log the new rating
        Log::info("New rating submitted by user {$rating->user_id} for rental {$rating->rental_id}");

        // Example: Auto-flag rating for admin review
        $rating->status = 'pending'; // status column in ratings table (pending / approved)
        $rating->saveQuietly(); // avoid infinite loop
    }

    /**
     * Handle the Rating "updated" event.
     *
     * @param  \App\Models\Rating  $rating
     * @return void
     */
    public function updated(Rating $rating)
    {
            if ($rating->isDirty('status') && $rating->status === 'approved') {
            Log::info("Rating {$rating->id} approved by admin.");
        }

    }

    /**
     * Handle the Rating "deleted" event.
     *
     * @param  \App\Models\Rating  $rating
     * @return void
     */
    public function deleted(Rating $rating)
    {
        //
    }

    /**
     * Handle the Rating "restored" event.
     *
     * @param  \App\Models\Rating  $rating
     * @return void
     */
    public function restored(Rating $rating)
    {
        //
    }

    /**
     * Handle the Rating "force deleted" event.
     *
     * @param  \App\Models\Rating  $rating
     * @return void
     */
    public function forceDeleted(Rating $rating)
    {
        //
    }
}
