<?php

namespace App\Http\Controllers;

use App\Services\BranchManagerDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchManagerDashboardController extends Controller
{
    public function __construct(private BranchManagerDashboardService $dashboard)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->isBranchManager()) {
            abort(403);
        }

        $selectedYear = (int) $request->get('year', date('Y'));
        $adminData = $this->dashboard->build($user, $selectedYear);

        return view('home', compact('adminData'));
    }
}
