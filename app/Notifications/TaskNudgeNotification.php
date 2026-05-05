<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskNudgeNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $task, $nudgeFrom;

    public function __construct($task, $nudgeFrom)
    {
        $this->task = $task;
        $this->nudgeFrom = $nudgeFrom;
    }

    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }

    public function toArray($notifiable)
    {
        return [
            'header' => 'Progress Update Requested',
            'category' => 'Urgent',
            'data' => "{$this->nudgeFrom->name} has requested a status update on your task: '{$this->task->title}'",
            'link' => url('/projects/task/' . base64_encode($this->task->id) . '/history'),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $notification = [
            'header' => 'Progress Update Requested',
            'category' => 'Urgent',
            'data' => "{$this->nudgeFrom->name} has requested a status update on your task: '{$this->task->title}'",
            'link' => url('/projects/task/' . base64_encode($this->task->id) . '/history'),
        ];
        return new BroadcastMessage(["notifications" => $notification]);
    }
}
