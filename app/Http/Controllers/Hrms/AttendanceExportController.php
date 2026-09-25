<?php

namespace App\Http\Controllers\Hrms;

use App\Exports\DateRangeAttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceExportController extends Controller
{
    public function index()
    {
        $startDate = Carbon::today()->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::today()->format('Y-m-d');

        $departments = [
            '' => 'All Departments',
            '1' => 'Sales (NSD)',
            '2' => 'Operations (OD)',
            '3' => 'Customer Success (CSD)',
        ];

        $employees = User::whereIn('status', User::WORKING_STATUSES)
            ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['Admin', 'Client']))
            ->with(['departments.dept'])
            ->orderBy('name', 'asc')
            ->get();

        return view('components.hrms.attendance_export.index', compact('startDate', 'endDate', 'departments', 'employees'));
    }

    public function export(Request $request)
    {
        $startDateStr = $request->input('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDateStr = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $departmentId = $request->input('department');
        $userId = $request->input('user_id') ? (int) $request->input('user_id') : null;

        $fileName = 'Attendance_Payroll_Export_' . $startDateStr . '_to_' . $endDateStr . '.xlsx';

        return Excel::download(
            new DateRangeAttendanceExport($startDateStr, $endDateStr, $departmentId, $userId),
            $fileName
        );
    }
}
