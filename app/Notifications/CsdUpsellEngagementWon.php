<?php

namespace App\Notifications;

use App\Models\ClientEngagement;
use App\Models\CsdOpportunity;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CsdUpsellEngagementWon extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public ClientEngagement $engagement,
        public CsdOpportunity $opportunity,
        public string $departmentName,
        public ?User $teamLeader = null,
        public ?User $winningExecutive = null
    ) {}

    public function via($notifiable): array
    {
        return ['broadcast', 'database'];
    }

    public function toArray($notifiable): array
    {
        $clientName = $this->engagement->clients?->name ?? 'Client';
        $type = ucfirst(str_replace('_', ' ', $this->opportunity->type ?? 'upsell'));
        $engNo = $this->engagement->engagement_no;
        $title = $this->opportunity->title;
        $isTeamLeader = $this->teamLeader && (int) $notifiable->id === (int) $this->teamLeader->id;

        if ($isTeamLeader) {
            $header = 'Upsell Won — Client Assigned to You';
            $data = "{$this->departmentName}: {$type} \"{$title}\" for {$clientName} ({$engNo}) is won. "
                . "This client is now under your management while NSD closes the commercial order.";
            $link = route('csd.clients.index');
        } else {
            $header = 'CSD Upsell / Cross-sell Tracked';
            $data = "{$this->departmentName}: {$type} \"{$title}\" for {$clientName} — order {$engNo} created. "
                . ($this->teamLeader
                    ? "Team Leader {$this->teamLeader->name} will manage the client."
                    : 'Awaiting Team Leader assignment.');
            $link = route('commercial.engagements.show', $this->engagement->id);
        }

        return [
            'header' => $header,
            'category' => 'CSD',
            'data' => $data,
            'link' => $link,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage(['notifications' => $this->toArray($notifiable)]);
    }
}
