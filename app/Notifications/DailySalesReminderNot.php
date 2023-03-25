<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailySalesReminderNot extends Notification implements  ShouldBroadcastNow
{
    use Queueable;

    public $client, $category, $history;


    public function __construct( $history, $client, $category)
    {
        $this->client = $client;
        $this->history  = $history;
        $this->category  = $category;
    }


    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }


    public function toArray($notifiable)
    {
        if($this->history->category == 'STS'){
            $urlParam = 'sts';
        }else{
            $urlParam = 'dsr';
        }

        return [
            'header' =>"New Reminder - {$this->client->name}",
            'category' => $this->category,
            'data' => "You have new {$this->history->category} {$this->history->status} TBRO reminder on {$this->history->tbro}",
            'link'=> env('APP_URL')."/clients/".base64_encode($this->client->id)."/{$urlParam}",
        ];
    }


    public function toBroadcast($notifiable): BroadcastMessage
    {
        if($this->history->category == 'STS'){
            $urlParam = 'sts';
        }else{
            $urlParam = 'dsr';
        }

        $notification = [
                'header' =>"New Reminder from - {$this->client->name}",
                'category' => $this->category,
                'data' => "You have new {$this->history->category} {$this->history->status} TBRO Reminder on {$this->history->tbro}",
                'link'=> env('APP_URL')."/clients/".base64_encode($this->client->id)."/{$urlParam}",
        ];
        return new BroadcastMessage([ "notifications" => $notification ]);
    }



}
