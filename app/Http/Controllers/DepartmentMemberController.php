<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentMember;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class DepartmentMemberController extends Controller
{

    public function edit(Request $request){
        $department = $request->member;
        $deptMember = DepartmentMember::select('user', 'is_leader', 'department')
                            ->where('id', $department)->first()->toArray();
        return response()->json(['status' =>true, 'data'=>$deptMember], 200);
    }

    public function statusMember(Request $request){
        $memberid = $request->id;

        $deptMember = DepartmentMember::where('id', $memberid)->first();
        if($deptMember->status ==  true){
            $deptMember->status = false;
            $deptMember->save();
            return redirect()->back()->with('success', 'Status updated');
        }else{
            $deptMember->status = true;
            $deptMember->save();
            return redirect()->back()->with('success', 'Status updated');
        }
    }

    public function deleteMember(Request $request){
        $memberid = $request->id;
        $deptMember = DepartmentMember::where('id', $memberid)->first();
        if($deptMember){
            $deptMember->to_date = Carbon::now();
            $deptMember->delete();
            return redirect()->back()->with('success', 'Member Deleted');
        }
    }




    // Drag& Drop


}
