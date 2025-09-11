<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Rating;

class AdminRepliedNotification extends Notification
{
    protected $rating;

    public function __construct(Rating $rating)
    {
        $this->rating = $rating;
    }

    public function via($notifiable)
    {
        return ['mail']; // 可以加 'database', 'broadcast' 等
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('You have a new reply from Admin')
                    ->line("Your rating for {$this->rating->vehicle->brand} {$this->rating->vehicle->model} has a reply:")
                    ->line($this->rating->adminreply)
                    ->action('View Rating', url("/ratings/{$this->rating->reservation_id}/view"));
    }
}
