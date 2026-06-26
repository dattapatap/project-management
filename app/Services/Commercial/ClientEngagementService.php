<?php

namespace App\Services\Commercial;

use App\Models\ClientEngagement;
use App\Models\ClientEngagementEvent;
use App\Models\ClientPackages;
use App\Models\ClientPayments;
use App\Models\Clients;
use App\Models\CsdClientAssignment;
use App\Models\CsdOpportunity;
use App\Models\DepartmentProjects;
use App\Models\User;
use App\Notifications\ClientMatured;
use App\Notifications\CsdOpportunityWon;
use App\Notifications\CsdUpsellEngagementWon;
use App\Services\BranchScopeService;
use App\Services\CsdTeamScopeService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ClientEngagementService
{
    public function __construct(
        private BranchScopeService $branchScope,
        private CsdTeamScopeService $csdScope
    ) {
    }

    public function listQuery(User $user): Builder
    {
        $query = ClientEngagement::with(['clients', 'salesOwner', 'csdOwner', 'parent', 'project'])
            ->latest();

        return $this->applyVisibilityScope($query, $user);
    }

    public function timelineForClient(int $clientId): \Illuminate\Database\Eloquent\Collection
    {
        return ClientEngagement::with(['parent', 'project', 'opportunity', 'events.creator'])
            ->where('client_id', $clientId)
            ->orderBy('id')
            ->get();
    }

    /**
     * CSD opportunity marked won → child engagement for NSD commercial closure.
     */
    public function spawnFromWonOpportunity(CsdOpportunity $opportunity): ClientEngagement
    {
        if ($opportunity->engagement_id) {
            return ClientEngagement::findOrFail($opportunity->engagement_id);
        }

        $clientId = (int) $opportunity->getAttributes()['client'];
        $client = Clients::findOrFail($clientId);
        $parent = $this->resolveParentEngagement($clientId);

        $engagement = DB::transaction(function () use ($opportunity, $client, $parent, $clientId) {
            $engagement = ClientEngagement::create([
                'engagement_no' => $this->nextEngagementNo(),
                'client_id' => $clientId,
                'parent_engagement_id' => $parent?->id,
                'root_engagement_id' => $parent?->root_engagement_id ?? $parent?->id,
                'source_type' => 'csd_opportunity',
                'source_id' => $opportunity->id,
                'opportunity_id' => $opportunity->id,
                'engagement_type' => $opportunity->type === 'cross_sell'
                    ? ClientEngagement::TYPE_CROSS_SELL
                    : ClientEngagement::TYPE_UPSELL,
                'title' => $opportunity->title,
                'description' => $opportunity->description,
                'status' => ClientEngagement::STATUS_WON_PENDING_COMMERCIAL,
                'estimated_value' => $opportunity->estimated_value,
                'sales_owner_id' => $client->ref_user,
                'csd_owner_id' => $opportunity->assigned_to,
                'won_at' => now(),
                'created_by' => Auth::id() ?? $opportunity->created_by,
            ]);

            $opportunity->update(['engagement_id' => $engagement->id]);

            $this->recordEvent($engagement, 'opportunity_won', null, $engagement->status,
                'Upsell opportunity won by CSD — awaiting NSD commercial closure.');

            return $engagement;
        });

        $engagement = $engagement->fresh(['clients', 'parent']);

        $winningExecutive = User::find($opportunity->assigned_to);
        $teamLeader = $this->handoffClientToCsdTeamLeader($clientId, $engagement, $opportunity, $winningExecutive);
        $this->notifyCsdDepartmentOnUpsellWon($engagement->fresh(['clients']), $opportunity, $teamLeader, $winningExecutive);
        $this->notifySalesOnEngagement($engagement->fresh(['clients']));

        return $engagement;
    }

    /**
     * First sale via NSD DSR Matured — root engagement record.
     */
    public function recordInitialFromMaturity(
        Clients $client,
        DepartmentProjects $project,
        ClientPackages $package,
        int $createdBy
    ): ClientEngagement {
        $existing = ClientEngagement::where('client_id', $client->id)
            ->where('engagement_type', ClientEngagement::TYPE_INITIAL)
            ->where('project_id', $project->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($client, $project, $package, $createdBy) {
            $engagement = ClientEngagement::create([
                'engagement_no' => $this->nextEngagementNo(),
                'client_id' => $client->id,
                'parent_engagement_id' => null,
                'root_engagement_id' => null,
                'source_type' => 'nsd_maturity',
                'source_id' => null,
                'engagement_type' => ClientEngagement::TYPE_INITIAL,
                'title' => $project->project_name ?? 'Initial Sale',
                'status' => ClientEngagement::STATUS_IN_DELIVERY,
                'closed_value' => $package->package,
                'project_id' => $project->id,
                'package_id' => $package->id,
                'sales_owner_id' => $client->ref_user,
                'commercial_closed_at' => now(),
                'delivery_started_at' => now(),
                'created_by' => $createdBy,
            ]);

            $engagement->update(['root_engagement_id' => $engagement->id]);
            $project->update(['engagement_id' => $engagement->id]);
            $package->update(['engagement_id' => $engagement->id]);

            $this->recordEvent($engagement, 'initial_maturity', null, $engagement->status,
                'Initial sale matured via NSD DSR.');

            return $engagement;
        });
    }

    public function startCommercial(ClientEngagement $engagement, User $user): ClientEngagement
    {
        $this->assertCanManageCommercial($engagement, $user);

        if (!in_array($engagement->status, [
            ClientEngagement::STATUS_WON_PENDING_COMMERCIAL,
            ClientEngagement::STATUS_COMMERCIAL_IN_PROGRESS,
        ], true)) {
            throw new \InvalidArgumentException('This engagement is not awaiting commercial work.');
        }

        return $this->transition($engagement, ClientEngagement::STATUS_COMMERCIAL_IN_PROGRESS,
            'commercial_started', 'NSD started commercial closure.');
    }

    /**
     * Close upsell commercially — creates project + package; client stays Matured.
     */
    public function closeCommercial(ClientEngagement $engagement, Request $request, User $user): ClientEngagement
    {
        $this->assertCanManageCommercial($engagement, $user);

        if (!in_array($engagement->status, [
            ClientEngagement::STATUS_WON_PENDING_COMMERCIAL,
            ClientEngagement::STATUS_COMMERCIAL_IN_PROGRESS,
        ], true)) {
            throw new \InvalidArgumentException('Only pending commercial engagements can be closed.');
        }

        $projectCat = DB::table('project_category')->where('id', $request->category)->first();
        $projectSub = DB::table('project_sub_categories')->where('id', $request->sub_category)->first();

        if (!$projectCat || !$projectSub) {
            throw new \InvalidArgumentException('Invalid project category or sub-category.');
        }

        return DB::transaction(function () use ($engagement, $request, $user, $projectCat, $projectSub) {
            $project = DepartmentProjects::create([
                'client' => $engagement->client_id,
                'engagement_id' => $engagement->id,
                'department' => $projectCat->dept_id,
                'category' => $request->category,
                'sub_category' => $request->sub_category,
                'assigned_by' => $user->id,
                'created_date' => now(),
                'project_name' => 'UPSELL: ' . $engagement->title,
                'start_date' => now(),
                'status' => 'ToDo',
                'description' => $engagement->description,
            ]);

            $package = ClientPackages::create([
                'client' => $engagement->client_id,
                'engagement_id' => $engagement->id,
                'project_id' => $project->id,
                'package' => $request->package,
                'balance' => round($request->package - $request->advance, 2),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $payment = new ClientPayments();
            $payment->client = $engagement->client_id;
            $payment->package_id = $package->id;
            $payment->paid_date = Carbon::now();
            $payment->amount = $request->advance;
            $payment->remains = round($request->package - $request->advance, 2);
            $payment->payment_type = $request->payment_type;
            $payment->created_by = $user->id;

            if ($request->payment_type === 'Online') {
                $payment->transactioinid = $request->transactionid;
            } elseif ($request->payment_type === 'Cheque' && $request->hasFile('payment_cheque_receipt')) {
                $name = 'payments/' . time() . '.' . $request->file('payment_cheque_receipt')->getClientOriginalExtension();
                $request->file('payment_cheque_receipt')->storeAs('clients', basename($name), 'public');
                $payment->file = 'clients/' . basename($name);
            } elseif ($request->payment_type === 'Cash' && $request->hasFile('payment_cash_receipt')) {
                $name = 'payments/' . time() . '.' . $request->file('payment_cash_receipt')->getClientOriginalExtension();
                $request->file('payment_cash_receipt')->storeAs('clients', basename($name), 'public');
                $payment->file = 'clients/' . basename($name);
            }

            $payment->save();

            $engagement->update([
                'status' => ClientEngagement::STATUS_IN_DELIVERY,
                'closed_value' => $request->package,
                'project_id' => $project->id,
                'package_id' => $package->id,
                'sales_owner_id' => $user->id,
                'commercial_closed_at' => now(),
                'delivery_started_at' => now(),
            ]);

            $this->recordEvent($engagement, 'commercial_closed', ClientEngagement::STATUS_COMMERCIAL_IN_PROGRESS,
                $engagement->status, 'NSD closed commercial — OD project #' . $project->id . ' created.');

            $productManagers = User::whereHas('roles', fn ($q) => $q->where('name', 'Project-Manager'))
                ->where('status', 'Active')->get();
            $client = Clients::find($engagement->client_id);
            foreach ($productManagers as $pm) {
                $pm->notify((new ClientMatured($client, $project, $projectSub->name))->delay(now()->addSeconds(5)));
            }

            return $engagement->fresh(['clients', 'project', 'package', 'parent', 'children']);
        });
    }

    public function markDeliveryCompleted(DepartmentProjects $project): void
    {
        $engagement = ClientEngagement::where('project_id', $project->id)->first();

        if (!$engagement || $engagement->status === ClientEngagement::STATUS_COMPLETED) {
            return;
        }

        $this->transition($engagement, ClientEngagement::STATUS_COMPLETED,
            'delivery_completed', 'OD project marked completed.');
        $engagement->update(['completed_at' => now()]);
    }

    public function recordEvent(
        ClientEngagement $engagement,
        string $eventType,
        ?string $fromStatus,
        ?string $toStatus,
        ?string $notes = null
    ): ClientEngagementEvent {
        return ClientEngagementEvent::create([
            'engagement_id' => $engagement->id,
            'event_type' => $eventType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus ?? $engagement->status,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }

    private function transition(ClientEngagement $engagement, string $toStatus, string $eventType, string $notes): ClientEngagement
    {
        $from = $engagement->status;
        $engagement->update(['status' => $toStatus]);
        $this->recordEvent($engagement, $eventType, $from, $toStatus, $notes);

        return $engagement->fresh();
    }

    private function resolveParentEngagement(int $clientId): ?ClientEngagement
    {
        return ClientEngagement::where('client_id', $clientId)
            ->whereIn('status', [
                ClientEngagement::STATUS_COMPLETED,
                ClientEngagement::STATUS_IN_DELIVERY,
                ClientEngagement::STATUS_COMMERCIAL_CLOSED,
            ])
            ->orderByDesc('id')
            ->first()
            ?? ClientEngagement::where('client_id', $clientId)
                ->where('engagement_type', ClientEngagement::TYPE_INITIAL)
                ->first();
    }

    private function nextEngagementNo(): string
    {
        $year = date('Y');
        $last = ClientEngagement::where('engagement_no', 'like', "ENG-{$year}-%")
            ->orderByDesc('id')
            ->value('engagement_no');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('ENG-%s-%04d', $year, $seq);
    }

    private function notifySalesOnEngagement(ClientEngagement $engagement): void
    {
        $client = $engagement->clients;
        if (!$client) {
            return;
        }

        $recipientIds = collect();
        if ($client->ref_user) {
            $recipientIds->push((int) $client->ref_user);
        }

        $anchorUser = $client->ref_user ? User::find($client->ref_user) : null;
        if ($anchorUser) {
            $recipientIds = $recipientIds->merge($this->branchScope->getBranchSalesUserIds($anchorUser));
        }

        $recipients = User::whereIn('id', $recipientIds->unique()->filter()->values())->get();
        if ($recipients->isEmpty()) {
            return;
        }

        $opportunity = $engagement->opportunity_id
            ? CsdOpportunity::find($engagement->opportunity_id)
            : null;

        if ($opportunity) {
            Notification::send($recipients, new CsdOpportunityWon($opportunity, $engagement));
        }
    }

    /**
     * Assign / reassign active CSD client record to the CSD Team Leader after upsell won.
     */
    private function handoffClientToCsdTeamLeader(
        int $clientId,
        ClientEngagement $engagement,
        CsdOpportunity $opportunity,
        ?User $winningExecutive
    ): ?User {
        if (!$winningExecutive) {
            return null;
        }

        $teamLeaders = $this->csdScope->resolveTeamLeadersForMember($winningExecutive);
        $teamLeader = $teamLeaders->first();

        if (!$teamLeader) {
            $this->recordEvent($engagement, 'csd_tl_missing', null, $engagement->status,
                'No CSD Team Leader found for winning executive — client assignment unchanged.');

            return null;
        }

        $note = sprintf(
            'Upsell/cross-sell won (%s): %s. Client assigned to TL %s for ongoing management.',
            $engagement->engagement_no,
            $opportunity->title,
            $teamLeader->name
        );

        $assignment = CsdClientAssignment::where('client', $clientId)->where('status', 'active')->first();

        if ($assignment) {
            $assignment->update([
                'assigned_to' => $teamLeader->id,
                'notes' => trim(($assignment->notes ?? '') . "\n" . $note),
            ]);
        } else {
            CsdClientAssignment::create([
                'client' => $clientId,
                'project_id' => $engagement->parent?->project_id,
                'assigned_to' => $teamLeader->id,
                'handoff_date' => now()->toDateString(),
                'health_status' => 'healthy',
                'status' => 'active',
                'notes' => $note,
                'created_by' => Auth::id() ?? $opportunity->created_by,
            ]);
        }

        $engagement->update([
            'csd_owner_id' => $teamLeader->id,
            'csd_team_leader_id' => $teamLeader->id,
        ]);

        $this->recordEvent($engagement, 'csd_tl_assigned', null, $engagement->status,
            "Client assigned to CSD Team Leader {$teamLeader->name}.");

        return $teamLeader;
    }

    /**
     * Notify CSD department users: TL (manage client), winning exec, branch CSD leaders.
     */
    private function notifyCsdDepartmentOnUpsellWon(
        ClientEngagement $engagement,
        CsdOpportunity $opportunity,
        ?User $teamLeader,
        ?User $winningExecutive
    ): void {
        $departmentName = $this->csdScope->getDepartmentLabel();
        $recipientIds = collect();

        if ($teamLeader) {
            $recipientIds->push($teamLeader->id);
        }
        if ($winningExecutive) {
            $recipientIds->push($winningExecutive->id);
        }

        if ($winningExecutive) {
            $branchId = $this->branchScope->resolveBranchId($winningExecutive);
            if ($branchId) {
                $csdBranchUsers = User::whereIn('id', function ($q) use ($branchId) {
                    $q->select('user')->from('user_branches')->where('branch', $branchId);
                })
                    ->whereHas('roles', fn ($r) => $r->where('name', 'Branch-Manager'))
                    ->pluck('id');
                $recipientIds = $recipientIds->merge($csdBranchUsers);
            }
        }

        if ($teamLeader) {
            $teamIds = $this->csdScope->getTeamMemberIds($teamLeader, true);
            $recipientIds = $recipientIds->merge($teamIds);
        }

        $recipients = User::whereIn('id', $recipientIds->unique()->filter()->values())
            ->where(function ($q) {
                $q->whereHas('departments', fn ($d) => $d->where('department', BranchScopeService::DEPT_CSD))
                    ->orWhereHas('roles', fn ($r) => $r->where('name', 'Branch-Manager'));
            })
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new CsdUpsellEngagementWon($engagement, $opportunity, $departmentName, $teamLeader, $winningExecutive)
        );
    }

    private function applyVisibilityScope(Builder $query, User $user): Builder
    {
        if ($user->isGlobalAdmin() || $user->isBranchManager()) {
            if ($user->isBranchManager()) {
                $salesIds = $this->branchScope->getBranchSalesUserIds($user);
                $csdIds = $this->branchScope->getBranchCsdUserIds($user);
                $userIds = array_unique(array_merge($salesIds, $csdIds));

                return $query->where(function ($q) use ($userIds, $user) {
                    $q->whereIn('sales_owner_id', $userIds)
                        ->orWhereIn('csd_owner_id', $userIds)
                        ->orWhereIn('created_by', $userIds)
                        ->orWhere('created_by', $user->id);
                });
            }

            return $query;
        }

        if ($user->hasRole('Sales-Executive')) {
            return $query->where('sales_owner_id', $user->id);
        }

        if ($user->hasRole('Team-Leader') && (int) ($user->departments->department ?? 0) === BranchScopeService::DEPT_NSD) {
            $ids = $this->branchScope->getBranchSalesUserIds($user);

            return $query->whereIn('sales_owner_id', $ids);
        }

        if ($user->hasRole(['CSD-Executive', 'Team-Leader']) && $this->csdScope->isCsdDepartmentUser($user)) {
            $ids = $this->csdScope->getScopedUserIds($user) ?? [$user->id];

            return $query->where(function ($q) use ($ids, $user) {
                $q->whereIn('csd_owner_id', $ids)
                    ->orWhereIn('csd_team_leader_id', $ids)
                    ->orWhereIn('created_by', $ids)
                    ->orWhereHas('opportunity', fn ($oq) => $oq->whereIn('assigned_to', $ids));
                if ($user->hasRole('Team-Leader')) {
                    $q->orWhere('csd_team_leader_id', $user->id);
                }
            });
        }

        return $query->where('created_by', $user->id);
    }

    private function assertCanManageCommercial(ClientEngagement $engagement, User $user): void
    {
        if ($user->isGlobalAdmin() || $user->isBranchManager()) {
            return;
        }

        if ($user->hasRole(['Sales-Executive', 'Team-Leader']) && (int) ($user->departments->department ?? 0) === BranchScopeService::DEPT_NSD) {
            if ((int) $engagement->sales_owner_id === (int) $user->id) {
                return;
            }
            if ($user->hasRole('Team-Leader')) {
                $ids = $this->branchScope->getBranchSalesUserIds($user);
                if (in_array((int) $engagement->sales_owner_id, $ids, true)) {
                    return;
                }
            }
        }

        throw new \InvalidArgumentException('You are not allowed to manage this commercial engagement.');
    }
}
