<?php

namespace App\Notifications;

use App\Models\CsdClientAssignment;
use App\Models\DepartmentProjects;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ProjectHandoffToCsd extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public DepartmentProjects $project,
        public CsdClientAssignment $assignment
    ) {}

    public function via($notifiable): array
    {
        return ['broadcast', 'database'];
    }

    public function toArray($notifiable): array
    {
        $clientName = $this->project->clients->name ?? 'Client';

        return [
            'header' => 'Project Ready for CSD Handoff',
            'category' => 'CSD',
            'data' => "Project '{$this->project->project_name}' for {$clientName} has been delivered and is ready for CSD follow-up.",
            'link' => url('/csd/clients'),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage(['notifications' => $this->toArray($notifiable)]);
    }
}
