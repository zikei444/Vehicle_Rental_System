<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Rating;
use App\Notifications\RatingReplied;

class UserObserver implements Observer
{
    protected $user;
    protected $rating;

    public function __construct(User $user, Rating $rating)
    {
        $this->user = $user;
        $this->rating = $rating;
    }

    public function update($message)
    {
        if ($this->user) {
            $this->user->notify(new RatingReplied($this->rating, $message));
        }
    }
}
