<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRatingCreated extends Notification
{
    use Queueable;

    protected $rating;

    public function __construct($rating)
    {
        $this->rating = $rating;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "New rating for {$this->rating->vehicle->brand} {$this->rating->vehicle->model}: {$this->rating->rating}⭐",
            'rating_id' => $this->rating->id,
            'vehicle_id' => $this->rating->vehicle_id,
        ];
    }
}
