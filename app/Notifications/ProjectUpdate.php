<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectUpdate extends Notification implements  ShouldBroadcastNow
{
    use Queueable;

    public $category, $project;


    public function __construct( $project, $category)
    {
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
            'header' =>'Project Update',
            'category' => $this->category,
            'data' => "Please check the project updated",
            'link'=> env('APP_URL')."/projects/".base64_encode($this->project->id),
        ];
    }


    public function toBroadcast($notifiable): BroadcastMessage
    {
        $notification = [
                'header' =>'Project Update',
                'category' =>  $this->category,
                'data' => "Please check the project updated",
                'link'=> env('APP_URL')."/projects/".base64_encode($this->project->id),
        ];
        return new BroadcastMessage([ "notifications" => $notification ]);
    }

}
