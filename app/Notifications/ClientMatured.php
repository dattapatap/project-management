<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientMatured extends Notification implements  ShouldBroadcastNow
{
    use Queueable;

    public $client, $category, $project;


    public function __construct($client, $project, $category)
    {
        $this->client = $client;
        $this->project  = $project;
        $this->category  = $category;
    }


    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }


    public function toArray($notifiable)
    {
        return [
            'header' =>'New Project has been Assigned',
            'category' => $this->category,
            'data' => "Please check the new '{$this->project->project_name}' has been assigned",
            'link'=> env('APP_URL')."/clients/".base64_encode($this->client->id)."/development",
        ];
    }


    public function toBroadcast($notifiable): BroadcastMessage
    {
        $notification = [
                'header' =>'New Project has been Assigned',
                'category' => $this->category,
                'data' => "Please check the new '{$this->project->project_name}' has been assigned",
                'link'=> env('APP_URL')."/clients/".base64_encode($this->client->id)."/development",
        ];
        return new BroadcastMessage([ "notifications" => $notification ]);
    }

}
