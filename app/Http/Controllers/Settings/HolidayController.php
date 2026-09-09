<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Services\Hrms\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HolidayController extends Controller
{
    protected HolidayService $holidayService;

    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    public function index(Request $request)
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $holidays = $this->holidayService->getHolidays($year);

        return view('components.settings.holidays.index', compact('holidays', 'year'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'holiday_date' => 'required|date|unique:holidays,holiday_date',
            'type' => 'required|in:National,Company,Optional',
            'description' => 'nullable|string|max:1000',
        ]);

        $this->holidayService->createHoliday($validated, Auth::user());

        return redirect()->route('settings.holidays.index')->with('success', 'Holiday added successfully.');
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'holiday_date' => 'required|date|unique:holidays,holiday_date,' . $holiday->id,
            'type' => 'required|in:National,Company,Optional',
            'description' => 'nullable|string|max:1000',
        ]);

        $this->holidayService->updateHoliday($holiday, $validated);

        return redirect()->route('settings.holidays.index')->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $this->holidayService->deleteHoliday($holiday);

        return redirect()->route('settings.holidays.index')->with('success', 'Holiday deleted successfully.');
    }
}
