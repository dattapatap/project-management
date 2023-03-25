<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function index(){
        $roles = Role::all()->toArray();
        return response()->json(['status'=>true, 'roles'=>$roles]);
    }

    public function rolesbydepartment(){
        $roles = Role::all()->toArray();
        return response()->json(['status'=>true, 'roles'=>$roles]);
    }


}
