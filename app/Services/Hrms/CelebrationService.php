<?php

namespace App\Services\Hrms;

use App\Models\Employees;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CelebrationService
{
    /**
     * Get employees celebrating their Birthday or Work Anniversary today.
     *
     * @return array{birthdays: Collection, anniversaries: Collection, total_count: int}
     */
    public function getTodaysCelebrations(): array
    {
        $today = Carbon::today();
        $todayMonth = (int) $today->month;
        $todayDay = (int) $today->day;

        $activeEmployees = $this->getActiveEmployeesQuery()->get();

        $birthdays = $activeEmployees->filter(function ($emp) use ($todayMonth, $todayDay, $today) {
            if (!$emp->dob) return false;
            try {
                $dob = Carbon::parse($emp->dob);
                return (int) $dob->month === $todayMonth && (int) $dob->day === $todayDay;
            } catch (\Throwable) {
                return false;
            }
        })->map(function ($emp) use ($today) {
            try {
                $dob = Carbon::parse($emp->dob);
                $emp->age = max(0, $today->year - $dob->year);
            } catch (\Throwable) {
                $emp->age = 0;
            }
            return $emp;
        })->values();

        $anniversaries = $activeEmployees->filter(function ($emp) use ($todayMonth, $todayDay, $today) {
            if (!$emp->joining_dt) return false;
            try {
                $joining = Carbon::parse($emp->joining_dt);
                $years = $today->year - $joining->year;
                return (int) $joining->month === $todayMonth && (int) $joining->day === $todayDay && $years > 0;
            } catch (\Throwable) {
                return false;
            }
        })->map(function ($emp) use ($today) {
            try {
                $joining = Carbon::parse($emp->joining_dt);
                $years = max(0, $today->year - $joining->year);
                $emp->years_completed = $years;
                $emp->milestone = $this->getMilestoneTitle($years);
            } catch (\Throwable) {
                $emp->years_completed = 0;
                $emp->milestone = 'Work Anniversary';
            }
            return $emp;
        })->values();

        return [
            'birthdays'     => $birthdays,
            'anniversaries' => $anniversaries,
            'total_count'   => $birthdays->count() + $anniversaries->count(),
        ];
    }

    /**
     * Get upcoming birthdays within the next N days (default 60 days / 2 months).
     */
    public function getUpcomingBirthdays(int $days = 60): Collection
    {
        $today = Carbon::today();
        $activeEmployees = $this->getActiveEmployeesQuery()->whereNotNull('dob')->get();

        $upcoming = $activeEmployees->map(function ($emp) use ($today) {
            try {
                $dob = Carbon::parse($emp->dob);
                $month = (int) $dob->month;
                $day = (int) $dob->day;

                // Safe day for Feb 29 in non-leap year
                if ($month === 2 && $day === 29 && !checkdate(2, 29, $today->year)) {
                    $day = 28;
                }

                $nextBirthday = Carbon::create($today->year, $month, $day)->startOfDay();

                // If birthday already passed this year, it falls in next year
                if ($nextBirthday->isPast() && !$nextBirthday->isToday()) {
                    $nextYear = $today->year + 1;
                    $nextDay = ($month === 2 && $day === 29 && !checkdate(2, 29, $nextYear)) ? 28 : $day;
                    $nextBirthday = Carbon::create($nextYear, $month, $nextDay)->startOfDay();
                }

                $diffDays = (int) $today->diffInDays($nextBirthday, false);
                $turningAge = $nextBirthday->year - $dob->year;

                $emp->next_date = $nextBirthday;
                $emp->next_birthday_carbon = $nextBirthday;
                $emp->days_away = $diffDays;
                $emp->days_until_birthday = $diffDays;
                $emp->turning_age = $turningAge;
                $emp->formatted_date = $nextBirthday->format('d M');
                $emp->day_name = $nextBirthday->format('l');

                return $emp;
            } catch (\Throwable) {
                return null;
            }
        })->filter(function ($emp) use ($days) {
            return $emp && $emp->days_away > 0 && $emp->days_away <= $days;
        })->sortBy('days_away')->values();

        return $upcoming;
    }

    /**
     * Get upcoming work anniversaries within the next N days (default 60 days / 2 months).
     */
    public function getUpcomingAnniversaries(int $days = 60): Collection
    {
        $today = Carbon::today();
        $activeEmployees = $this->getActiveEmployeesQuery()->whereNotNull('joining_dt')->get();

        $upcoming = $activeEmployees->map(function ($emp) use ($today) {
            try {
                $joining = Carbon::parse($emp->joining_dt);
                $month = (int) $joining->month;
                $day = (int) $joining->day;

                if ($month === 2 && $day === 29 && !checkdate(2, 29, $today->year)) {
                    $day = 28;
                }

                $nextAnniversary = Carbon::create($today->year, $month, $day)->startOfDay();

                if ($nextAnniversary->isPast() && !$nextAnniversary->isToday()) {
                    $nextYear = $today->year + 1;
                    $nextDay = ($month === 2 && $day === 29 && !checkdate(2, 29, $nextYear)) ? 28 : $day;
                    $nextAnniversary = Carbon::create($nextYear, $month, $nextDay)->startOfDay();
                }

                $diffDays = (int) $today->diffInDays($nextAnniversary, false);
                $completingYears = $nextAnniversary->year - $joining->year;

                $emp->next_date = $nextAnniversary;
                $emp->next_anniversary_carbon = $nextAnniversary;
                $emp->days_away = $diffDays;
                $emp->completing_years = $completingYears;
                $emp->years_completed = $completingYears;
                $emp->formatted_date = $nextAnniversary->format('d M');
                $emp->day_name = $nextAnniversary->format('l');
                $emp->milestone = $this->getMilestoneTitle($completingYears);

                return $emp;
            } catch (\Throwable) {
                return null;
            }
        })->filter(function ($emp) use ($days) {
            return $emp && $emp->days_away > 0 && $emp->days_away <= $days && $emp->completing_years > 0;
        })->sortBy('days_away')->values();

        return $upcoming;
    }

    /**
     * Group all active employees by tenure milestone club.
     */
    public function getTenureMilestoneClubs(): array
    {
        $today = Carbon::today();
        $employees = $this->getActiveEmployeesQuery()->whereNotNull('joining_dt')->get();

        $clubs = [
            'diamond' => ['title' => '5+ Years Club (Veterans)', 'color' => 'purple', 'badge' => 'badge-soft-purple', 'icon' => 'mdi-diamond-stone', 'members' => collect()],
            'gold'    => ['title' => '3 - 4 Years Club', 'color' => 'warning', 'badge' => 'badge-soft-warning', 'icon' => 'mdi-medal-outline', 'members' => collect()],
            'silver'  => ['title' => '2 Years Club', 'color' => 'info', 'badge' => 'badge-soft-info', 'icon' => 'mdi-seal', 'members' => collect()],
            'bronze'  => ['title' => '1 Year Club (Pioneers)', 'color' => 'success', 'badge' => 'badge-soft-success', 'icon' => 'mdi-star-outline', 'members' => collect()],
            'fresh'   => ['title' => '< 1 Year (Rising Stars)', 'color' => 'secondary', 'badge' => 'badge-soft-secondary', 'icon' => 'mdi-sprout-outline', 'members' => collect()],
        ];

        foreach ($employees as $emp) {
            $joining = Carbon::parse($emp->joining_dt);
            $years = $today->diffInYears($joining);
            $months = $today->diffInMonths($joining);
            $emp->tenure_years = $years;
            $emp->tenure_formatted = $years >= 1 ? "{$years} " . ($years > 1 ? 'Years' : 'Year') : "{$months} " . ($months > 1 ? 'Months' : 'Month');

            if ($years >= 5) {
                $clubs['diamond']['members']->push($emp);
            } elseif ($years >= 3) {
                $clubs['gold']['members']->push($emp);
            } elseif ($years >= 2) {
                $clubs['silver']['members']->push($emp);
            } elseif ($years >= 1) {
                $clubs['bronze']['members']->push($emp);
            } else {
                $clubs['fresh']['members']->push($emp);
            }
        }

        return $clubs;
    }

    /**
     * Get summary metrics for celebrations.
     */
    public function getCelebrationStats(int $days = 60): array
    {
        $today = Carbon::today();
        $todays = $this->getTodaysCelebrations();
        $upcomingBirthdays = $this->getUpcomingBirthdays($days);
        $upcomingAnniversaries = $this->getUpcomingAnniversaries($days);

        return [
            'today_birthdays'          => $todays['birthdays']->count(),
            'today_anniversaries'      => $todays['anniversaries']->count(),
            'upcoming_birthdays_count' => $upcomingBirthdays->count(),
            'upcoming_anniv_count'     => $upcomingAnniversaries->count(),
            'this_month_name'          => $today->format('F Y'),
        ];
    }

    /**
     * Base query with eager-loaded user account, department, and branch.
     * Strictly filters for currently active employees with active user accounts.
     */
    private function getActiveEmployeesQuery()
    {
        return Employees::whereIn('status', Employees::WORKING_STATUSES)
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('end_dt')
                  ->orWhere('end_dt', '>=', Carbon::today()->format('Y-m-d'));
            })
            ->whereHas('userAccount', function ($q) {
                $q->whereIn('status', \App\Models\User::WORKING_STATUSES)
                  ->whereNull('deleted_at');
            })
            ->with([
                'userAccount.departments.dept',
                'userAccount.branch.branch',
                'userAccount.roles',
            ]);
    }

    /**
     * Helper to get milestone label.
     */
    private function getMilestoneTitle(int $years): string
    {
        if ($years >= 5) return "5+ Years Milestone (Diamond)";
        if ($years === 4) return "4th Year Milestone";
        if ($years === 3) return "3rd Year Milestone (Gold)";
        if ($years === 2) return "2nd Year Milestone (Silver)";
        if ($years === 1) return "1st Year Milestone (Pioneer)";
        return "Work Anniversary";
    }
}
