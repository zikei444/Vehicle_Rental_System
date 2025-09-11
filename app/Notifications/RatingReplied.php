<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RatingReplied extends Notification
{
    use Queueable;

    protected $rating;
    protected $reply;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($rating, $reply)
    {
        $this->rating = $rating;  
        $this->reply = $reply;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database']; // only in-app notifications
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toDatabase($notifiable)
    {
         return [
            'title'       => 'Rating Replied',
            'message'     => "Your rating for {$this->rating->vehicle->brand} {$this->rating->vehicle->model} has received a reply.",
            'admin_reply' => $this->rating->adminreply,
            'url'         => route('reservations.rating.show', $this->rating->reservation_id), // 👈 点击通知跳到 ratinglog 页面
            'time'        => now()->format('d M Y H:i'),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
        public function toArray($notifiable)
        {
            return $this->toDatabase($notifiable);
        }

}
