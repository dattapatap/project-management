<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class StatusLiked extends Notification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $username;

    public $message;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($username)
    {
        $this->username = $username;
        $this->message  = "{$username} liked your status";
    }


    public function via($notifiable): array
    {
        return ['broadcast', 'database', 'mail'];
    }

    public function toArray($notifiable)
    {
        return [
            'header' =>'New Order has been placed',
            'data' => 'Please check the new order and take the action',
            'link'=> env('APP_URL').'/admin/orders/all',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $notification = [
                'header' =>'New User Registered',
                'data' => 'Please check the new '. $this->user->rolecode .' has registred',
                'link'=> env('APP_URL').'/admin/notifications',
        ];
        return new BroadcastMessage([ "notifications" => $notification ]);
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('notifications');
    }

}
