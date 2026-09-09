<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\BranchScopeService;
use App\Services\Reports\OdWorkReportService;
use App\Services\Reports\NsdWorkReportService;
use App\Services\Reports\CsdWorkReportService;
use App\Services\Reports\ReportDateRangeService;
use App\Services\Reports\ReportScopeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class OperationsReportController extends Controller
{
    public function __construct(
        private ReportScopeService $scope,
        private ReportDateRangeService $dateRange,
        private OdWorkReportService $odWork,
        private NsdWorkReportService $nsdWork,
        private CsdWorkReportService $csdWork
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasBranchWideAccess() && !$user->hasRole(['Project-Manager', 'Team-Leader'])) {
            abort(403, 'Unauthorized access.');
        }

        $startDateInput = $request->input('start_date', $request->input('date_from'));
        $endDateInput = $request->input('end_date', $request->input('date_to'));

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate = Carbon::parse($endDateInput)->endOfDay();
        } else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfDay();
        }

        $startDateStr = $startDate->toDateString();
        $endDateStr = $endDate->toDateString();
        $range = [
            'from' => $startDate,
            'to' => $endDate,
            'preset' => 'custom',
            'label' => $startDate->format('d M, Y') . ' – ' . $endDate->format('d M, Y'),
        ];

        $departmentId = (int) $request->get('department', BranchScopeService::DEPT_OD);
        $departments = [
            BranchScopeService::DEPT_OD => 'Operations (OD)',
            BranchScopeService::DEPT_NSD => 'Sales (NSD)',
            BranchScopeService::DEPT_CSD => 'Customer Success (CSD)',
        ];

        if (!$user->hasBranchWideAccess()) {
            $departmentId = (int) ($user->departments->department ?? BranchScopeService::DEPT_OD);
        }

        $employees = $this->scope->visibleEmployeesQuery($user, $departmentId)->get();
        $employeeCount = $employees->count();
        $branchLabel = $user->isGlobalAdmin() ? 'All Branches' : 'Your Branch';

        // Enrich summary statistics
        if ($departmentId === BranchScopeService::DEPT_NSD) {
            $enriched = $employees->map(fn ($emp) => $this->nsdWork->enrichEmployeeRow($emp, $startDate, $endDate));
            $totalOutput = $enriched->sum('matured_count');
            $outputLabel = 'Matured Clients';
            $totalHours = $enriched->sum('leads_count');
            $hoursLabel = 'Total Leads';
        } elseif ($departmentId === BranchScopeService::DEPT_CSD) {
            $enriched = $employees->map(fn ($emp) => $this->csdWork->enrichEmployeeRow($emp, $startDate, $endDate));
            $totalOutput = $enriched->sum('tickets_resolved');
            $outputLabel = 'Tickets Resolved';
            $totalHours = $enriched->sum('comms_count');
            $hoursLabel = 'Client Comms';
        } else {
            $enriched = $employees->map(fn ($emp) => $this->odWork->enrichEmployeeRow($emp, $startDate, $endDate));
            $totalOutput = $enriched->sum('completed_tasks');
            $outputLabel = 'Completed Tasks';
            $totalHours = round((float) $enriched->sum('total_hours'), 1);
            $hoursLabel = 'Total Hours Logged';
        }

        $avgProductivity = $employeeCount > 0 ? round($enriched->avg('productivity')) : 0;

        return view('components.reports.operations', compact(
            'range', 'departmentId', 'departments', 'employeeCount', 'branchLabel',
            'startDateStr', 'endDateStr', 'totalOutput', 'outputLabel', 'totalHours', 'hoursLabel', 'avgProductivity'
        ));
    }

    public function data(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasBranchWideAccess() && !$user->hasRole(['Project-Manager', 'Team-Leader'])) {
            abort(403);
        }

        $startDateInput = $request->input('start_date', $request->input('date_from'));
        $endDateInput = $request->input('end_date', $request->input('date_to'));

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate = Carbon::parse($endDateInput)->endOfDay();
        } else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfDay();
        }

        $departmentId = (int) $request->get('department', BranchScopeService::DEPT_OD);

        if (!$user->hasBranchWideAccess()) {
            $departmentId = (int) ($user->departments->department ?? BranchScopeService::DEPT_OD);
        }

        $employees = $this->scope->visibleEmployeesQuery($user, $departmentId)->get();

        if ($departmentId === BranchScopeService::DEPT_NSD) {
            $employees = $employees->map(fn ($emp) => $this->nsdWork->enrichEmployeeRow($emp, $startDate, $endDate));
        } elseif ($departmentId === BranchScopeService::DEPT_CSD) {
            $employees = $employees->map(fn ($emp) => $this->csdWork->enrichEmployeeRow($emp, $startDate, $endDate));
        } else {
            $employees = $employees->map(fn ($emp) => $this->odWork->enrichEmployeeRow($emp, $startDate, $endDate));
        }

        return DataTables::of($employees)
            ->addIndexColumn()
            ->addColumn('action_link', function ($row) use ($startDate, $endDate, $departmentId) {
                return route('reports.employee.detail', [
                    'id' => base64_encode($row->id),
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'date_from' => $startDate->toDateString(),
                    'date_to' => $endDate->toDateString(),
                    'department' => $departmentId,
                ]);
            })
            ->make(true);
    }
}
