<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class ProjectSubCategoryController extends Controller
{

    public function getcategorybyid(Request $request){
        $deptid = $request->projcategory;

        $category = DB::table('project_sub_categories')
                            ->select('id', 'name as text')
                            ->where('proj_id', $deptid)
                            ->where('deleted_at', null)
                            ->orderBy('id', 'asc')->get();

        if($category){
            return response()->json(['status'=>true, 'data'=>$category]);
        }else{
            return response()->json(['status'=>false]);
        }


    }



}
