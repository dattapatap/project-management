<?php

namespace App\Http\Controllers\Hrms;

use App\Http\Controllers\Controller;
use App\Services\Hrms\HolidayViewService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HolidayViewController extends Controller
{
    public function __construct(
        private HolidayViewService $holidayService
    ) {}

    /**
     * Display the Annual Holiday List & Calendar.
     */
    public function index(Request $request)
    {
        $year = (int) ($request->get('year', Carbon::now()->year));
        $holidays = $this->holidayService->getHolidaysForYear($year);
        $nextHoliday = $this->holidayService->getNextUpcomingHoliday();
        $stats = $this->holidayService->getHolidayStats($year);
        $availableYears = $this->holidayService->getAvailableYears();

        return view('components.hrms.holidays.index', compact(
            'holidays',
            'nextHoliday',
            'stats',
            'availableYears',
            'year'
        ));
    }

    /**
     * JSON feed for FullCalendar.
     */
    public function events(Request $request): JsonResponse
    {
        $year = (int) ($request->get('year', Carbon::now()->year));
        $events = $this->holidayService->getCalendarEvents($year);

        return response()->json($events);
    }
}
