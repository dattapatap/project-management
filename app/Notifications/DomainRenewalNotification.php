<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DomainRenewalNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $domain, $days;

    public function __construct($domain, $days)
    {
        $this->domain = $domain;
        $this->days = $days;
    }

    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }

    public function toArray($notifiable)
    {
        $message = "Domain {$this->domain->domain} is expiring in {$this->days} days ({$this->domain->expiry_dt}).";
        if ($this->days == 0) {
            $message = "Domain {$this->domain->domain} expires TODAY!";
        }

        return [
            'header' => "Domain Renewal Alert",
            'category' => "Domain",
            'data' => $message,
            'link' => url('/csd/renewals'),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $message = "Domain {$this->domain->domain} is expiring in {$this->days} days ({$this->domain->expiry_dt}).";
        if ($this->days == 0) {
            $message = "Domain {$this->domain->domain} expires TODAY!";
        }

        $notification = [
            'header' => "Domain Renewal Alert",
            'category' => "Domain",
            'data' => $message,
            'link' => url('/csd/renewals'),
        ];
        return new BroadcastMessage(["notifications" => $notification]);
    }
}
