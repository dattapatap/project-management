<?php

namespace App\Http\Controllers;

use App\Models\DepartmentProjects;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request){
        $projects = DepartmentProjects::get();

        return view('components.projects.index', compact('projects'));

    }
}
