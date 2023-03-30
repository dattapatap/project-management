<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements  ShouldBroadcastNow
{
    use Queueable;

    public $category, $task;


    public function __construct( $task, $category)
    {
        $this->task  = $task;
        $this->category  = $category;
    }


    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }


    public function toArray($notifiable)
    {
        return [
            'header' =>'New Task has been Assigned',
            'category' => $this->category,
            'data' => "Please check the new task has been assigned",
            'link'=> env('APP_URL')."/mytasks/".base64_encode($this->task->id),
        ];
    }


    public function toBroadcast($notifiable): BroadcastMessage
    {
        $notification = [
                'header' =>'New Task has been Assigned',
                'category' => $this->category,
                'data' => "Please check the new task has been assigned",
                'link'=> env('APP_URL')."/mytasks/".base64_encode($this->task->id),
        ];
        return new BroadcastMessage([ "notifications" => $notification ]);
    }

}
