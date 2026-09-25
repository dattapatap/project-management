<?php

namespace App\Services\Hrms;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HolidayViewService
{
    /**
     * Get all holidays for a given year with enriched metadata.
     */
    public function getHolidaysForYear(int $year): Collection
    {
        $today = Carbon::today();

        return Holiday::whereYear('holiday_date', $year)
            ->orderBy('holiday_date', 'asc')
            ->get()
            ->map(function ($holiday) use ($today) {
                $date = Carbon::parse($holiday->holiday_date);
                $holiday->carbon_date = $date;
                $holiday->formatted_date = $date->format('d M Y');
                $holiday->day_name = $date->format('l');
                $holiday->month_name = $date->format('F');
                $holiday->is_past = $date->isPast() && !$date->isToday();
                $holiday->is_today = $date->isToday();
                $holiday->days_diff = (int) $today->diffInDays($date, false);
                $holiday->type_badge = $this->getTypeBadgeClass($holiday->type);
                $holiday->is_long_weekend = in_array($date->dayOfWeek, [Carbon::FRIDAY, Carbon::MONDAY]);

                return $holiday;
            });
    }

    /**
     * Get the next upcoming company holiday from today.
     */
    public function getNextUpcomingHoliday(): ?object
    {
        $today = Carbon::today();

        $holiday = Holiday::where('holiday_date', '>=', $today->toDateString())
            ->orderBy('holiday_date', 'asc')
            ->first();

        if (!$holiday) {
            return null;
        }

        $date = Carbon::parse($holiday->holiday_date);
        $daysAway = (int) $today->diffInDays($date, false);

        return (object) [
            'id'             => $holiday->id,
            'name'           => $holiday->name,
            'type'           => $holiday->type,
            'type_badge'     => $this->getTypeBadgeClass($holiday->type),
            'description'    => $holiday->description,
            'date'           => $date,
            'formatted_date' => $date->format('l, d F Y'),
            'day_name'       => $date->format('l'),
            'days_away'      => $daysAway,
            'is_today'       => $daysAway === 0,
            'is_tomorrow'    => $daysAway === 1,
            'is_long_weekend'=> in_array($date->dayOfWeek, [Carbon::FRIDAY, Carbon::MONDAY]),
        ];
    }

    /**
     * Get holiday metrics for the specified year.
     */
    public function getHolidayStats(int $year): array
    {
        $today = Carbon::today();
        $all = $this->getHolidaysForYear($year);

        $totalCount = $all->count();
        $remainingCount = $all->where('is_past', false)->count();
        $pastCount = $all->where('is_past', true)->count();
        $longWeekends = $all->where('is_long_weekend', true)->count();

        return [
            'total'         => $totalCount,
            'remaining'     => $remainingCount,
            'past'          => $pastCount,
            'long_weekends' => $longWeekends,
            'year'          => $year,
        ];
    }

    /**
     * Get distinct years available in holidays table + surrounding years.
     */
    public function getAvailableYears(): array
    {
        $currentYear = (int) Carbon::now()->year;
        $dbYears = Holiday::selectRaw('YEAR(holiday_date) as year')
            ->distinct()
            ->pluck('year')
            ->toArray();

        $years = array_unique(array_merge([$currentYear - 1, $currentYear, $currentYear + 1], $dbYears));
        sort($years);

        return $years;
    }

    /**
     * Get calendar events array for FullCalendar.
     */
    public function getCalendarEvents(int $year): array
    {
        $holidays = $this->getHolidaysForYear($year);

        return $holidays->map(function ($h) {
            $color = match (strtolower($h->type ?? '')) {
                'national'   => '#e74c3c',
                'gazetted'   => '#e67e22',
                'restricted' => '#9b59b6',
                'optional'   => '#3498db',
                default      => '#27ae60',
            };

            return [
                'id'              => $h->id,
                'title'           => '🏖️ ' . $h->name,
                'start'           => Carbon::parse($h->holiday_date)->format('Y-m-d'),
                'allDay'          => true,
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'type'        => $h->type,
                    'description' => $h->description ?? '',
                    'day'         => $h->day_name,
                ],
            ];
        })->toArray();
    }

    /**
     * Map holiday type to badge CSS class.
     */
    private function getTypeBadgeClass(?string $type): string
    {
        return match (strtolower($type ?? '')) {
            'national', 'national holiday' => 'badge-soft-danger',
            'gazetted', 'gazetted holiday' => 'badge-soft-warning',
            'restricted'                   => 'badge-soft-purple',
            'optional'                     => 'badge-soft-info',
            default                        => 'badge-soft-success',
        };
    }
}
