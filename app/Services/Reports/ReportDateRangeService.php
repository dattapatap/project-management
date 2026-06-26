<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportDateRangeService
{
    /**
     * @return array{from: Carbon, to: Carbon, preset: string, label: string}
     */
    public function resolve(Request $request): array
    {
        $preset = $request->get('preset', $request->get('range', 'monthly'));
        $year = (int) $request->get('year', date('Y'));
        $month = $request->get('month', 'All');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        return $this->resolveFromParams($preset, $year, $month, $dateFrom, $dateTo);
    }

    /**
     * @return array{from: Carbon, to: Carbon, preset: string, label: string}
     */
    public function resolveFromParams(
        string $preset,
        int $year,
        string $month = 'All',
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $now = Carbon::now();

        if ($preset === 'daily' || $preset === 'today') {
            return [
                'from' => $now->copy()->startOfDay(),
                'to' => $now->copy()->endOfDay(),
                'preset' => 'daily',
                'label' => 'Today · ' . $now->format('d M Y'),
            ];
        }

        if ($preset === 'weekly') {
            return [
                'from' => $now->copy()->startOfWeek(),
                'to' => $now->copy()->endOfWeek(),
                'preset' => 'weekly',
                'label' => 'This Week · ' . $now->copy()->startOfWeek()->format('d M') . ' – ' . $now->copy()->endOfWeek()->format('d M Y'),
            ];
        }

        if ($preset === 'monthly' && $month === 'All' && !$dateFrom) {
            return [
                'from' => $now->copy()->startOfMonth(),
                'to' => $now->copy()->endOfMonth(),
                'preset' => 'monthly',
                'label' => $now->format('F Y'),
            ];
        }

        if ($preset === 'custom' && $dateFrom && $dateTo) {
            $from = Carbon::parse($dateFrom)->startOfDay();
            $to = Carbon::parse($dateTo)->endOfDay();

            return [
                'from' => $from,
                'to' => $to,
                'preset' => 'custom',
                'label' => $from->format('d M Y') . ' – ' . $to->format('d M Y'),
            ];
        }

        if ($month !== 'All') {
            $from = Carbon::createFromDate($year, (int) date('m', strtotime($month)), 1)->startOfMonth();
            $to = $from->copy()->endOfMonth();

            return [
                'from' => $from,
                'to' => $to,
                'preset' => 'monthly',
                'label' => $month . ' ' . $year,
            ];
        }

        $from = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $to = Carbon::createFromDate($year, 12, 31)->endOfYear();

        return [
            'from' => $from,
            'to' => $to,
            'preset' => 'yearly',
            'label' => 'FY ' . $year,
        ];
    }
}
