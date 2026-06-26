<?php

namespace App\Http\Controllers\Csd;

use App\Http\Controllers\Controller;
use App\Services\Csd\CsdTeamReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CsdTeamReportController extends Controller
{
    public function __construct(private CsdTeamReportService $report)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->isBranchManager() && !$user->hasRole('Team-Leader')) {
            abort(403);
        }

        $year = (int) $request->get('year', date('Y'));
        $month = $request->get('month', 'All');
        $selectedUserId = $request->filled('user') ? (int) $request->get('user') : null;

        $reportData = $this->report->getReportData($user, $selectedUserId, $year, $month);

        return view('components.csd.reports.team', compact('reportData'));
    }
}
