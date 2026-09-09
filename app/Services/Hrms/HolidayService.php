<?php

namespace App\Services\Hrms;

use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HolidayService
{
    /**
     * Get list of holidays for current or specific year.
     */
    public function getHolidays(?int $year = null): Collection
    {
        $year = $year ?: Carbon::now()->year;

        return Holiday::whereYear('holiday_date', $year)
            ->orderBy('holiday_date', 'asc')
            ->with('creator')
            ->get();
    }

    /**
     * Store new holiday.
     */
    public function createHoliday(array $data, ?User $user = null): Holiday
    {
        return Holiday::create([
            'name' => $data['name'],
            'holiday_date' => Carbon::parse($data['holiday_date'])->format('Y-m-d'),
            'type' => $data['type'] ?? 'Company',
            'description' => $data['description'] ?? null,
            'created_by' => $user ? $user->id : null,
        ]);
    }

    /**
     * Update existing holiday.
     */
    public function updateHoliday(Holiday $holiday, array $data): bool
    {
        return $holiday->update([
            'name' => $data['name'],
            'holiday_date' => Carbon::parse($data['holiday_date'])->format('Y-m-d'),
            'type' => $data['type'] ?? 'Company',
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Delete a holiday.
     */
    public function deleteHoliday(Holiday $holiday): bool
    {
        return $holiday->delete();
    }

    /**
     * Check if a given date is a holiday.
     */
    public function isHoliday(string $dateStr): bool
    {
        return Holiday::where('holiday_date', $dateStr)->exists();
    }

    /**
     * Get Holiday for date.
     */
    public function getHolidayForDate(string $dateStr): ?Holiday
    {
        return Holiday::where('holiday_date', $dateStr)->first();
    }
}
