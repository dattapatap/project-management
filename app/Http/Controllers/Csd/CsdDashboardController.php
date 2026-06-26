<?php

namespace App\Http\Controllers\Csd;

use App\Http\Controllers\Controller;
use App\Services\Csd\CsdDashboardService;
use App\Services\CsdTeamScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CsdDashboardController extends Controller
{
    public function __construct(private CsdDashboardService $dashboard)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $departmentId = $user->departments->department ?? null;
        $selectedYear = (int) $request->get('year', date('Y'));
        $adminData = [];
        $personalData = [];

        if ($user->hasRole('Team-Leader') && $departmentId == CsdTeamScopeService::DEPARTMENT_ID) {
            $adminData = $this->dashboard->getTeamLeaderData($user, $selectedYear);
            $personalData = $this->dashboard->getExecutiveData($user, $selectedYear);
        } elseif ($user->hasRole('CSD-Executive')) {
            $adminData = $this->dashboard->getExecutiveData($user, $selectedYear);
        } else {
            abort(403);
        }

        return view('home', compact('adminData', 'personalData'));
    }

    public function getBranchDashboardData($user): array
    {
        return $this->dashboard->getBranchManagerData($user);
    }

    public function getDashboardData($user): array
    {
        if ($user->hasRole('Team-Leader') && ($user->departments->department ?? null) == CsdTeamScopeService::DEPARTMENT_ID) {
            return $this->dashboard->getTeamLeaderData($user, (int) date('Y'));
        }

        return $this->dashboard->getExecutiveData($user, (int) date('Y'));
    }
}
