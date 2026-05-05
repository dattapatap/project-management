<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectTaskNotification extends Notification implements \Illuminate\Contracts\Broadcasting\ShouldBroadcastNow
{
    use Queueable;

    protected $details;

    /**
     * Create a new notification instance.
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Standard channels: Database and Broadcast
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->details['category'] ?? 'Project',
            'header'   => $this->details['header'] ?? 'New Notification',
            'data'     => $this->details['body'] ?? '',
            'link'     => $this->details['link'] ?? url('/'),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable)
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'notifications' => [
                'header' => $this->details['header'] ?? 'New Notification',
                'link'   => $this->details['link'] ?? url('/'),
            ]
        ]);
    }
}
