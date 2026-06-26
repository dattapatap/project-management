<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\BranchScopeService;
use App\Services\Reports\OdWorkReportService;
use App\Services\Reports\ReportDateRangeService;
use App\Services\Reports\ReportScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class OperationsReportController extends Controller
{
    public function __construct(
        private ReportScopeService $scope,
        private ReportDateRangeService $dateRange,
        private OdWorkReportService $odWork
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasBranchWideAccess() && !$user->hasRole(['Project-Manager', 'Team-Leader'])) {
            abort(403, 'Unauthorized access.');
        }

        $range = $this->dateRange->resolve($request);
        $departmentId = (int) $request->get('department', BranchScopeService::DEPT_OD);
        $departments = [
            BranchScopeService::DEPT_OD => 'Operations (OD)',
            BranchScopeService::DEPT_NSD => 'Sales (NSD)',
            BranchScopeService::DEPT_CSD => 'Customer Success (CSD)',
        ];

        if (!$user->hasBranchWideAccess()) {
            $departmentId = (int) ($user->departments->department ?? BranchScopeService::DEPT_OD);
        }

        $employeeCount = $this->scope->visibleEmployeesQuery($user, $departmentId)->count();
        $branchLabel = $user->isGlobalAdmin() ? 'All Branches' : 'Your Branch';

        return view('components.reports.operations', compact(
            'range', 'departmentId', 'departments', 'employeeCount', 'branchLabel'
        ));
    }

    public function data(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasBranchWideAccess() && !$user->hasRole(['Project-Manager', 'Team-Leader'])) {
            abort(403);
        }

        $range = $this->dateRange->resolve($request);
        $departmentId = (int) $request->get('department', BranchScopeService::DEPT_OD);

        if (!$user->hasBranchWideAccess()) {
            $departmentId = (int) ($user->departments->department ?? BranchScopeService::DEPT_OD);
        }

        $employees = $this->scope->visibleEmployeesQuery($user, $departmentId)
            ->get()
            ->map(fn ($emp) => $this->odWork->enrichEmployeeRow($emp, $range['from'], $range['to']));

        return DataTables::of($employees)
            ->addIndexColumn()
            ->addColumn('action_link', function ($row) use ($range, $departmentId) {
                return route('reports.employee.detail', [
                    'id' => base64_encode($row->id),
                    'preset' => $range['preset'],
                    'date_from' => $range['from']->toDateString(),
                    'date_to' => $range['to']->toDateString(),
                    'department' => $departmentId,
                ]);
            })
            ->make(true);
    }
}
