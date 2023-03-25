<?php

namespace App\Http\Controllers;

use App\Models\Branches;
use App\Models\Department;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{

    public function index()
    {
        $branches = Branches::where('status', true)->get();

        $departments = Department::with('branch', 'users.userdetail:name,id,profile')->where('deleted_at', null)->paginate(50);
        return view('components.department.index', compact('departments', 'branches'))->with('filter', '');
    }

    public function filterDepartment(Request $request){
        $filter = $request->query('department-filter');
        if (!empty($filter)) {
            $departments = Department::with('members', 'manager')
                        ->orwhere('gtin_no', 'like', '%'.$filter.'%')
                        ->orwhere('batch_no_detail', 'like', '%'.$filter.'%')
                        ->orwhere('sscc_code', 'like', '%'.$filter.'%')
                        ->orwhere('item_code', 'like', '%'.$filter.'%')
                        ->where('deleted_at', null)
                        ->orderBy('id', 'desc')->paginate(100);
        } else {
            $departments = Department::with('members', 'manager')
                        ->where('deleted_at', null)->orderBy('id', 'desc')->paginate(20);
        }
        return view('components.batch.index')->with('batches', $departments)->with('filter', $filter);
    }



    public function create()
    {

    }

    public function store(Request $request)
    {
        $rules = array(
            'name' => 'required|string',
            'branch' => 'required|numeric',
            'description' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array(
                'status' => 400,
                'errors' => $validator->getMessageBag()->toArray()
            ), 400);
        }else{
            try{
                if($request->department_id == ''){
                    $oldDept = Department::where('name', $request->post('name') )->where('branchid',  $request->post('branch'))
                                            ->where('deleted_at', null)->first();
                    if($oldDept){
                        return response()->json(['code'=>200, "status"=>false, 'message'=> "Duplicate Department Names" ], 200);
                    }else{

                        $department = new Department();
                        $department->name = $request->post('name');
                        $department->branchid = $request->post('branch');
                        $department->description = $request->post('description');
                        $department->status = true;
                        $department->save();
                        return response()->json(['code'=>200, "status"=>true, 'message'=> "Department Created" ], 200);
                    }
                }else{
                    $oldDept = Department::where('name', $request->post('name') )->where('branchid',  $request->post('branch'))
                                            ->where('deleted_at', null)
                                            ->where('id', '!=',  $request->department_id )->first();
                    if($oldDept){
                        return response()->json(['code'=>200, "status"=>false, 'message'=> "Duplicate Department Name" ], 200);
                    }else{
                        $department = Department::where('id', $request->department_id)->first();
                        $department->name = $request->post('name');
                        $department->branchid = $request->post('branch');
                        $department->description = $request->post('description');
                        $department->save();
                        return response()->json(['code'=>200, "status"=>true, 'message'=> "Department Updated" ], 200);
                    }
                }
                exit();
            }catch(Exception $ex){
                return response()->json(['code'=>201, 'status'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }
    }

    public function show(Department $department)
    {
        $departments = Department::with('branch', 'users')
                        ->where('id', $department->id)->first();
        return view('components.department.department', compact('departments'));
    }

    public function edit(Department $department)
    {
        $department = Department::where('id', $department->id)->first();
        if($department)
             return response()->json(["status"=>true, 'data'=> $department ], 200);
        else
            return response()->json(['code'=>200, "status"=>false, 'message'=> "Department Not Found" ], 200);
    }

    public function update(Request $request, Department $department)
    {
        //
    }

    public function destroy(Department $department)
    {
        $dept_id = $department->id;
        $dept = Department::find($dept_id);
        $dept->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted');
    }
}
