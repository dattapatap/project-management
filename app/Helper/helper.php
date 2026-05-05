<?php


// Sales Executive

use App\Models\ClientDomains;
use App\Models\Clients;
use App\Models\DepartmentProjects;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

function getTotalSales($user, $role){

    if($role == 'Admin')
       $sales =  DB::table('clients')->where('status', 'Matured')->count();
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
    $expired = ClientDomains::with('clients')
            ->where('expiry_dt', '<=', Carbon::today() )
            ->where('renewed', false)->count();

    return $expired;
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
            $q->whereHas('category', function($sq) use ($userDeptId) {
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

?>
