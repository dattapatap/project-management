<?php

namespace App\Notifications;

use App\Models\ClientEngagement;
use App\Models\CsdOpportunity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CsdOpportunityWon extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public CsdOpportunity $opportunity,
        public ?ClientEngagement $engagement = null
    ) {}

    public function via($notifiable): array
    {
        return ['broadcast', 'database'];
    }

    public function toArray($notifiable): array
    {
        $clientName = $this->opportunity->clients?->name ?? 'Client';
        $title = $this->opportunity->title;
        $type = ucfirst(str_replace('_', ' ', $this->opportunity->type ?? 'opportunity'));
        $engNo = $this->engagement?->engagement_no ?? '';

        return [
            'header' => 'Upsell Won — Commercial Order Created',
            'category' => 'Sales',
            'data' => "{$type} \"{$title}\" for {$clientName} is won. Close commercial order {$engNo} (client stays Matured — new project will be created).",
            'link' => $this->engagement
                ? route('commercial.engagements.show', $this->engagement->id)
                : route('commercial.engagements.index'),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage(['notifications' => $this->toArray($notifiable)]);
    }
}
