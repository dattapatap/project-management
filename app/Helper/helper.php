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



function projects($category, $user)
{
    if($user->hasRole('Project-Manager')){
        if($category == 'ALL')
            return DepartmentProjects::count();

        return DepartmentProjects::where('status', $category)->count();
    }

    if($user->hasRole('Team-Leader')){
        $projects = DepartmentProjects::where('status', $category)->count();
    }

}

function tasks($category, $user)
{
    if($user->hasRole('Project-Manager')){
        if($category == 'ALL')
            return Task::count();

        return Task::where('status', $category)->count();
    }

    if($user->hasRole('Team-Leader')){
        $projects = Task::where('status', $category)->count();
    }

}



?>
