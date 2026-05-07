<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class SalesLeadNudgeNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $client, $nudgeFrom;

    public function __construct($client, $nudgeFrom)
    {
        $this->client = $client;
        $this->nudgeFrom = $nudgeFrom;
    }

    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }

    public function toArray($notifiable)
    {
        return [
            'header' => 'Lead Follow-up Requested',
            'category' => 'Urgent',
            'data' => "{$this->nudgeFrom->name} has requested an immediate follow-up/status update on client: '{$this->client->name}'",
            'link' => route('client.detail', [base64_encode($this->client->id), 'sts']),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $notification = [
            'header' => 'Lead Follow-up Requested',
            'category' => 'Urgent',
            'data' => "{$this->nudgeFrom->name} has requested an immediate follow-up/status update on client: '{$this->client->name}'",
            'link' => route('client.detail', [base64_encode($this->client->id), 'sts']),
        ];
        return new BroadcastMessage(["notifications" => $notification]);
    }
}
