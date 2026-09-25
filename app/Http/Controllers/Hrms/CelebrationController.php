<?php

namespace App\Http\Controllers\Hrms;

use App\Http\Controllers\Controller;
use App\Services\Hrms\CelebrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CelebrationController extends Controller
{
    public function __construct(
        private CelebrationService $celebrationService
    ) {}

    /**
     * Display the Celebrations Hub page.
     */
    public function index()
    {
        $todays = $this->celebrationService->getTodaysCelebrations();
        $upcomingBirthdays = $this->celebrationService->getUpcomingBirthdays(60);
        $upcomingAnniversaries = $this->celebrationService->getUpcomingAnniversaries(60);
        $tenureClubs = $this->celebrationService->getTenureMilestoneClubs();
        $stats = $this->celebrationService->getCelebrationStats(60);

        return view('components.hrms.celebrations.index', compact(
            'todays',
            'upcomingBirthdays',
            'upcomingAnniversaries',
            'tenureClubs',
            'stats'
        ));
    }

    /**
     * API endpoint for dashboard widgets.
     */
    public function apiUpcoming(): JsonResponse
    {
        return response()->json([
            'todays'      => $this->celebrationService->getTodaysCelebrations(),
            'birthdays'   => $this->celebrationService->getUpcomingBirthdays(15),
            'anniversary' => $this->celebrationService->getUpcomingAnniversaries(15),
            'stats'       => $this->celebrationService->getCelebrationStats(),
        ]);
    }
}
