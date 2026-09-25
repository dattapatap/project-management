<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminAttendancesExport implements FromArray, WithHeadings, ShouldAutoSize
{
    private DateRangeAttendanceExport $exporter;

    public function __construct(string $dateStr, ?string $departmentId = null, ?string $statusFilter = null, ?\App\Models\User $actingUser = null, ?string $locationFilter = null, ?int $userId = null)
    {
        if (str_contains($dateStr, ' - ')) {
            $parts = explode(' - ', $dateStr);
            $startDateStr = trim($parts[0]);
            $endDateStr = trim($parts[1]);
        } else {
            $startDateStr = $dateStr ?: Carbon::today()->format('Y-m-d');
            $endDateStr = $dateStr ?: Carbon::today()->format('Y-m-d');
        }

        $this->exporter = new DateRangeAttendanceExport(
            $startDateStr,
            $endDateStr,
            $departmentId,
            $userId,
            $statusFilter,
            $actingUser,
            $locationFilter
        );
    }

    public function array(): array
    {
        return $this->exporter->array();
    }

    public function headings(): array
    {
        return $this->exporter->headings();
    }
}
