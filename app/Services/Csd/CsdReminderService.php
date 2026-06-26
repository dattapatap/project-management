<?php

namespace App\Services\Csd;

use App\Models\CsdAmcContract;
use App\Models\CsdCollectionFollowup;
use App\Models\CsdCommunication;
use App\Models\CsdRenewal;
use App\Models\CsdSupportTicket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class CsdReminderService
{
    public function sendAll(): array
    {
        return [
            'communication_followups' => $this->sendCommunicationFollowups(),
            'overdue_collections' => $this->sendOverdueCollectionAlerts(),
            'sla_breaches' => $this->sendSlaBreachAlerts(),
            'expiring_amc' => $this->sendAmcExpiryReminders(),
            'due_renewals' => $this->sendRenewalReminders(),
        ];
    }

    public function sendCommunicationFollowups(): int
    {
        $count = 0;
        $today = Carbon::today();

        $comms = CsdCommunication::with('client')
            ->whereNotNull('next_followup')
            ->whereDate('next_followup', '<=', $today)
            ->get();

        foreach ($comms as $comm) {
            $users = $this->stakeholdersForClient($comm->client);
            foreach ($users as $user) {
                $user->notify(new \App\Notifications\CsdGenericReminder(
                    'Communication Follow-up Due',
                    "Follow-up due for {$comm->client?->name}: {$comm->subject}",
                    url('/csd/communications')
                ));
                $count++;
            }
        }

        return $count;
    }

    public function sendOverdueCollectionAlerts(): int
    {
        $count = 0;
        $collections = CsdCollectionFollowup::with(['client', 'assignee'])
            ->where('status', 'overdue')
            ->get();

        foreach ($collections as $item) {
            $recipients = collect([$item->assignee])->filter();
            $recipients = $recipients->merge($this->stakeholdersForClient($item->client));
            foreach ($recipients->unique('id') as $user) {
                $user->notify(new \App\Notifications\CsdGenericReminder(
                    'Overdue Collection',
                    "Collection overdue for {$item->client?->name} — ₹" . number_format($item->amount_due, 2),
                    url('/csd/collections')
                ));
                $count++;
            }
        }

        return $count;
    }

    public function sendSlaBreachAlerts(): int
    {
        $count = 0;
        $tickets = CsdSupportTicket::with('client')
            ->whereIn('status', ['open', 'in_progress'])
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->get();

        foreach ($tickets as $ticket) {
            $recipients = collect([$ticket->assignee])->filter();
            $recipients = $recipients->merge($this->stakeholdersForClient($ticket->client));
            foreach ($recipients->unique('id') as $user) {
                $user->notify(new \App\Notifications\CsdGenericReminder(
                    'SLA Breach',
                    "Ticket {$ticket->ticket_no} breached SLA — {$ticket->client?->name}",
                    url('/csd/support')
                ));
                $count++;
            }
        }

        return $count;
    }

    public function sendAmcExpiryReminders(): int
    {
        $count = 0;
        $today = Carbon::today();

        $contracts = CsdAmcContract::with('client')
            ->where('status', 'active')
            ->get()
            ->filter(fn ($contract) => $contract->isExpiringSoon($today));

        foreach ($contracts as $contract) {
            $days = $contract->reminderDays();
            foreach ($this->stakeholdersForClient($contract->client) as $user) {
                $user->notify(new \App\Notifications\CsdGenericReminder(
                    'AMC / Support Contract Expiring',
                    ucfirst($contract->billing_cycle) . " contract for {$contract->client?->name} expires on {$contract->end_date->format('d M Y')} (reminder: {$days} days before).",
                    url('/csd/amc')
                ));
                $count++;
            }
        }

        return $count;
    }

    public function sendRenewalReminders(): int
    {
        $count = 0;
        $today = Carbon::today();

        $renewals = CsdRenewal::with('client')
            ->whereIn('status', ['upcoming', 'due'])
            ->whereDate('due_date', '<=', $today->copy()->addDays(14))
            ->get();

        foreach ($renewals as $renewal) {
            foreach ($this->stakeholdersForClient($renewal->client) as $user) {
                $user->notify(new \App\Notifications\CsdGenericReminder(
                    'Renewal Due',
                    "{$renewal->title} for {$renewal->client?->name} due {$renewal->due_date->format('d M Y')}",
                    url('/csd/renewals')
                ));
                $count++;
            }
        }

        return $count;
    }

    private function stakeholdersForClient(?int $clientId): \Illuminate\Support\Collection
    {
        if (!$clientId) {
            return collect();
        }

        $client = \App\Models\Clients::find($clientId);
        if (!$client) {
            return collect();
        }

        return app(CsdHandoffService::class)->getBranchStakeholdersForClient($client);
    }
}
