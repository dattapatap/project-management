<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CsdGenericReminder extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public string $header,
        public string $body,
        public string $link
    ) {}

    public function via($notifiable): array
    {
        return ['broadcast', 'database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'header' => $this->header,
            'category' => 'CSD',
            'data' => $this->body,
            'link' => $this->link,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage(['notifications' => $this->toArray($notifiable)]);
    }
}
