<?php

namespace App\Http\Controllers;

use App\Models\DepartmentProjectHistory;
use App\Models\DepartmentProjects;
use App\Models\User;
use App\Notifications\ClientMatured;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;

class DepartmentProjectHistoryController extends Controller
{
    public function index(){

    }

    public static function store( $project, $comment, $user){
            //Create Client Package
            $history                   = new DepartmentProjectHistory();
            $history->histories()->associate($project);
            $history->comments        = $comment;
            $history->date            = Carbon::now();
            $history->addedby         = $user;
            $history->save();
    }


}
