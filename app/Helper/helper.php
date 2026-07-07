<?php


// Sales Executive

use App\Models\Clients;
use App\Models\DepartmentProjects;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

function getTotalSales($user, $role){

    if($role == 'Admin')
       $sales =  DB::table('clients')->where('status', 'Matured')->count();
    elseif($role == 'Branch-Manager') {
        $branchScope = app(\App\Services\BranchScopeService::class);
        $salesUserIds = $branchScope->getBranchSalesUserIds($user);
        $sales = DB::table('clients')->where('status', 'Matured')
            ->where(function ($q) use ($salesUserIds) {
                $q->whereIn('ref_user', $salesUserIds)
                    ->orWhereIn('tele_ref_user', $salesUserIds);
            })->count();
    }
    elseif($role == 'Sales-Executive')
        $sales =  DB::table('clients')->where('ref_user', $user->id)->where('status', 'Matured')->count();
    elseif($role == 'Team-Leader')
        $sales =  DB::table('clients')->where('tele_ref_user', $user->id)->where('status', 'Matured')->count();
    else
        $sales =  DB::table('clients')->where('ref_user', $user->id)->where('status', 'Matured')->count();

     return $sales;
}

function getTbrosOfToday($user){
   $tbros = Clients::whereNotIn('status', ['Fresh','Matured', 'Not Interested'])
                    ->whereHas('histories', function($q) use($user){
                        $q->where('tbro' , '=',  Carbon::today()->toDateString());
                        $q->where('created', $user->id);
                    })
                    ->with(['histories' => function($query) use($user){
                        $query->where('tbro' , '=',  Carbon::today()->toDateString());
                        $query->where('created', $user->id);
                    }])
                    ->count();
   return $tbros;
}

function expiredDomains(){
    return \App\Models\CsdRenewal::whereIn('status', ['due', 'upcoming'])
        ->whereDate('due_date', '<=', Carbon::today())
        ->count();
}



function projects($category, $user, $year = null)
{
    if($user->hasRole('Project-Manager')){
        $query = DepartmentProjects::query();
        if ($year) {
            $query->whereYear('created_date', '<=', $year)
                  ->where(function($q) use ($year) {
                      $q->whereNull('act_end_date')->orWhereYear('act_end_date', '>=', $year);
                  });
        }
        
        if($category == 'ALL')
            return $query->count();

        return $query->where('status', $category)->count();
    }

    if($user->hasRole('Team-Leader')){
        $teamMember = App\Models\TeamMembers::where('user', $user->id)->where('status', true)->first();
        $teamId = $teamMember ? $teamMember->team : null;
        $userDeptId = $user->departments->department ?? null;

        $query = DepartmentProjects::where(function($q) use ($user, $teamId) {
            $q->where('assigned_to', $user->id);
            if ($teamId) {
                $q->orWhereHas('project_team', function($sq) use ($teamId) {
                    $sq->where('teamid', $teamId);
                });
            }
        })->when($userDeptId, function($q) use ($userDeptId) {
            $q->whereHas('projectCategory', function($sq) use ($userDeptId) {
                $sq->where('dept_id', $userDeptId);
            });
        });

        if ($category != 'ALL') {
            $query->where('status', $category);
        }
        
        if ($year) {
            $query->whereYear('created_date', '<=', $year)
                  ->where(function($q) use ($year) {
                      $q->whereNull('act_end_date')->orWhereYear('act_end_date', '>=', $year);
                  });
        }

        return $query->count();
    }
}

function tasks($category, $user, $year = null)
{
    if($user->hasRole('Project-Manager')){
        $query = Task::query();
        if ($year) {
            $query->whereYear('created_at', '<=', $year)
                  ->where(function($q) use ($year) {
                      $q->whereNull('act_enddate')->orWhereYear('act_enddate', '>=', $year);
                  });
        }
        
        if($category == 'ALL')
            return $query->count();

        return $query->where('status', $category)->count();
    }

    if($user->hasRole('Team-Leader')){
        $teamMember = App\Models\TeamMembers::where('user', $user->id)->where('status', true)->first();
        if (!$teamMember) return 0;
        $teamId = $teamMember->team;

        $query = Task::whereHas('user.teamMember', function($q) use ($teamId) {
            $q->where('team', $teamId);
        });

        if ($category != 'ALL') {
            $query->where('status', $category);
        }
        
        if ($year) {
            $query->whereYear('created_at', '<=', $year)
                  ->where(function($q) use ($year) {
                      $q->whereNull('act_enddate')->orWhereYear('act_enddate', '>=', $year);
                  });
        }

        return $query->count();
    }
}


function isAdminOrTeamLeader($user) {
    if (!$user) return false;
    return $user->hasRole(['Admin', 'Team-Leader']);
}

/**
 * Map URL segment (slug or legacy value) to internal client list category.
 */
function normalize_client_category(?string $segment): string
{
    if ($segment === null || $segment === '') {
        return 'Fresh';
    }

    $slug = strtolower(rawurldecode($segment));
    $slug = preg_replace('/[\s_]+/', '-', $slug);

    return match ($slug) {
        'fresh' => 'Fresh',
        'matured' => 'Matured',
        'not-interested', 'notinterested' => 'Not Interested',
        'followup', 'folloup', 'follow-up' => 'followup',
        default => $segment,
    };
}

/**
 * Canonical URL slug for a client list category.
 */
function client_category_slug(string $category): string
{
    return match (normalize_client_category($category)) {
        'Fresh' => 'fresh',
        'Matured' => 'matured',
        'Not Interested' => 'not-interested',
        'followup' => 'followup',
        default => \Illuminate\Support\Str::slug($category),
    };
}

function client_list_url(string $category = 'Fresh'): string
{
    return route('clients.category', client_category_slug($category));
}

function is_client_list_route(): bool
{
    if (!request()->is('client/*')) {
        return false;
    }

    $segment = strtolower((string) request()->segment(2));
    $reserved = ['history', 'docs', 'payment', 'ajax-create', 'createprojecct'];

    return $segment !== '' && !in_array($segment, $reserved, true);
}

function current_client_category_slug(): ?string
{
    if (!is_client_list_route()) {
        return null;
    }

    return client_category_slug(normalize_client_category(request()->segment(2)));
}

function client_category_active(string $slug): bool
{
    return current_client_category_slug() === $slug;
}

?>
