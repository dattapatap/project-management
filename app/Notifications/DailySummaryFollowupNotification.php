<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DailySummaryFollowupNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $count;

    public function __construct($count)
    {
        $this->count = $count;
    }

    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }

    public function toArray($notifiable)
    {
        return [
            'header' => "Today's Follow-up Queue ⏰",
            'category' => 'Schedule',
            'data' => "You have {$this->count} client follow-ups scheduled for today. Access your live queue to take action!",
            'link' => url('/home'),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $notification = [
            'header' => "Today's Follow-up Queue ⏰",
            'category' => 'Schedule',
            'data' => "You have {$this->count} client follow-ups scheduled for today. Access your live queue to take action!",
            'link' => url('/home'),
        ];
        return new BroadcastMessage(["notifications" => $notification]);
    }
}
