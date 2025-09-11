<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RatingReplied extends Notification
{
    use Queueable;

    protected $rating;
    protected $reply;

    public function __construct($rating, $reply)
    {
        $this->rating = $rating;
        $this->reply = $reply;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "Admin replied to your rating for {$this->rating->vehicle->brand} {$this->rating->vehicle->model}: {$this->reply}",
            'rating_id' => $this->rating->id,
            'vehicle_id' => $this->rating->vehicle_id,
            'admin_reply' => $this->reply,
        ];
    }
}
