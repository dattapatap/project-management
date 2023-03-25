<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignToExecutive extends Notification implements ShouldQueue, ShouldBroadcastNow
{
    use Queueable;

    public $client, $category;


    public function __construct($client, $category)
    {
        $this->client = $client;
        $this->category  = $category;
    }


    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }


    public function toArray($notifiable)
    {
        return [
            'header' =>'New Client has been Assigned',
            'category' => $this->category,
            'data' => "Please check the new '{$this->client->name}' has been assigned to you",
            'link'=> env('APP_URL')."/clients/".base64_encode($this->client->id)."/contacts",
        ];
    }


    public function toBroadcast($notifiable): BroadcastMessage
    {
        $notification = [
                'header' =>'New Client has been Assigned',
                'category' => $this->category,
                'data' => "Please check the new '{$this->client->name}' has been assigned to you",
                'link'=> env('APP_URL')."/clients/".base64_encode($this->client->id)."/contacts",
        ];
        return new BroadcastMessage([ "notifications" => $notification ]);
    }

}
