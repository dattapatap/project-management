<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clients;
use App\Models\TeamMembers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the Sales Department dashboard.
     */
    public function index(Request $request, \App\Services\Sales\TargetService $targetService)
    {
        $user = Auth::user();
        $selectedYear = $request->get('year', date('Y'));
        $selectedMonth = $request->get('month', date('n'));
        $departmentId = $user->departments->department ?? null;

        $adminData = [];
        $personalData = [];

        if ($user->hasRole('Team-Leader') && $departmentId == 1) {
            // 💼 Sales Department Team Leader Dashboard - Loads both Team Oversight and Personal Workspace
            $adminData = $this->getSalesTLDashboardData($user, $selectedYear);
            $adminData['leaderboard'] = $targetService->getLeaderboardData($selectedMonth, $selectedYear);
            $personalData = $this->getSalesExecutiveDashboardData($user, $selectedYear);
        } elseif ($user->hasRole('Sales-Executive')) {
            // 📞 Sales Executive (Employee) Dashboard
            $adminData = $this->getSalesExecutiveDashboardData($user, $selectedYear);
        }

        return view('home', compact('adminData', 'personalData'));
    }

    /* =========================================================================
     * 💼 SALES DEPARTMENT TEAM LEADER DATA LOADER
     * ========================================================================= */
    private function getSalesTLDashboardData($user, $selectedYear)
    {
        $adminData = [];

        // Find teams managed by the Team Leader
        $teams = DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();

        // Find all Sales Executives registered in those teams
        $allMembers = TeamMembers::whereIn('team', $teams)
            ->where('status', true)
            ->pluck('user')
            ->toArray();

        if (!in_array($user->id, $allMembers)) {
            $allMembers[] = $user->id;
        }

        // 1. Badge metrics: Total matured sales for the whole team (Filtered by selected year)
        $adminData['total_sales'] = Clients::whereIn('ref_user', $allMembers)
            ->where('status', 'Matured')
            ->whereYear('updated_at', $selectedYear)
            ->count();

        // 2. Badge metrics: Today's scheduled TBROs for the whole team
        $adminData['todays_tbros_count'] = Clients::whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
            ->whereHas('histories', function ($q) use ($allMembers) {
                $q->where('tbro', Carbon::today()->toDateString());
                $q->whereIn('created', $allMembers);
            })
            ->count();

        // 2b. Badge metrics: Overdue TBRO callbacks for the whole team
        $adminData['overdue_tbros_count'] = Clients::whereIn('ref_user', $allMembers)
            ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
            ->whereHas('histories', function ($q) use ($allMembers) {
                $q->where('tbro', '<', Carbon::today()->toDateString());
                $q->whereIn('created', $allMembers);
            })
            ->count();

        // 2c. Badge metrics: Total active leads under negotiation across the team
        $adminData['total_active_leads'] = Clients::whereIn('ref_user', $allMembers)
            ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
            ->count();

        // 3. Oversight Performance Matrix (The Heatmap)
        $adminData['team_performance_matrix'] = User::whereIn('id', $allMembers)
            ->withCount(['clients as active_leads_count' => function ($q) {
                $q->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested']);
            }])
            ->withCount(['clients as matured_leads_count' => function ($q) use ($selectedYear) {
                $q->where('status', 'Matured')
                    ->whereYear('updated_at', $selectedYear);
            }])
            ->withCount(['clients as today_callbacks_count' => function ($q) {
                $q->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
                    ->whereHas('histories', function ($sq) {
                        $sq->where('tbro', Carbon::today()->toDateString());
                    });
            }])
            ->withCount(['clients as overdue_callbacks_count' => function ($q) {
                $q->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
                    ->whereHas('histories', function ($sq) {
                        $sq->where('tbro', '<', Carbon::today()->toDateString());
                    });
            }])
            ->get();

        // 4. Lead Allocation Panel (Unassigned/Fresh leads)
        $adminData['unassigned_fresh_leads'] = Clients::with(['telereferral', 'creator'])
            ->where(function ($query) use ($allMembers, $user) {
                $query->whereIn('ref_user', $allMembers);
                $query->orWhere('tele_ref_user', $user->id);
            })
            ->where('status', 'Fresh')
            ->latest()
            ->limit(50)
            ->get();

        // 5. Allocatable team members list
        $adminData['allocatable_team_members'] = User::whereIn('id', $allMembers)
            ->where('id', '!=', $user->id)
            ->get();

        // 6. Today's Scheduled Callbacks list for the whole team
        $adminData['todays_callbacks'] = Clients::whereIn('ref_user', $allMembers)
            ->whereHas('histories', function ($q) use ($allMembers) {
                $q->where('tbro', Carbon::today()->toDateString());
                $q->whereIn('created', $allMembers);
            })
            ->with(['telereferral', 'referral', 'histories' => function ($q) {
                $q->orderBy('id', 'desc');
            }])
            ->get();

        // 7. Stage Distribution for Chart
        $statusDistribution = [
            'Fresh' => Clients::whereIn('ref_user', $allMembers)->where('status', 'Fresh')->whereYear('created_at', $selectedYear)->count(),
            'Followup' => Clients::whereIn('ref_user', $allMembers)->where('status', 'Followup')->whereYear('created_at', $selectedYear)->count(),
            'Meeting Fixed' => Clients::whereIn('ref_user', $allMembers)->where('status', 'Meeting Fixed')->whereYear('created_at', $selectedYear)->count(),
            'Matured' => Clients::whereIn('ref_user', $allMembers)->where('status', 'Matured')->whereYear('updated_at', $selectedYear)->count(),
            'Not Interested' => Clients::whereIn('ref_user', $allMembers)->where('status', 'Not Interested')->whereYear('updated_at', $selectedYear)->count(),
        ];
        $adminData['status_distribution'] = $statusDistribution;

        // 8. Year select configurations
        $startYear = $user->created_at ? $user->created_at->year : date('Y');
        if ($startYear > (int)date('Y') - 3) {
            $startYear = (int)date('Y') - 3; // Ensure at least 3 historical years
        }
        $adminData['available_years'] = range((int)date('Y'), $startYear);
        $adminData['selected_year'] = (int)$selectedYear;

        return $adminData;
    }

    /* =========================================================================
     * 📞 SALES EXECUTIVE (EMPLOYEE) DATA LOADER
     * ========================================================================= */
    private function getSalesExecutiveDashboardData($user, $selectedYear)
    {
        $adminData = [];

        // 1. My Daily Pulse metrics (Filtered by selected year where appropriate)
        $adminData['total_leads'] = Clients::where('ref_user', $user->id)
            ->whereYear('created_at', $selectedYear)
            ->count();

        $adminData['matured_leads'] = Clients::where('ref_user', $user->id)
            ->where('status', 'Matured')
            ->whereYear('updated_at', $selectedYear)
            ->count();

        $adminData['todays_callbacks_count'] = Clients::where('ref_user', $user->id)
            ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
            ->whereHas('histories', function ($q) use ($user) {
                $q->where('tbro', Carbon::today()->toDateString());
                $q->where('created', $user->id);
            })
            ->count();

        $adminData['overdue_callbacks_count'] = Clients::where('ref_user', $user->id)
            ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
            ->whereHas('histories', function ($q) use ($user) {
                $q->where('tbro', '<', Carbon::today()->toDateString());
                $q->where('created', $user->id);
            })
            ->count();

        // Count of clients with active DSR logs in the selected year (representing actual engaged leads)
        $dsr_leads_count = Clients::where('ref_user', $user->id)
            ->whereHas('histories', function ($q) use ($user, $selectedYear) {
                $q->where('category', 'DSR')
                    ->where('created', $user->id)
                    ->whereYear('created_at', $selectedYear);
            })
            ->count();

        // Calculate conversion rate specifically based on engaged DSR leads to filter out uncontacted/junk/cold STS assignments
        if ($dsr_leads_count > 0) {
            $adminData['conversion_rate'] = round(($adminData['matured_leads'] / $dsr_leads_count) * 100, 3);
        } else {
            // Safe fallback to all-time DSR-engaged leads if selected year has no DSR logs yet
            $all_time_dsr_count = Clients::where('ref_user', $user->id)
                ->whereHas('histories', function ($q) use ($user) {
                    $q->where('category', 'DSR')
                        ->where('created', $user->id);
                })
                ->count();

            $adminData['conversion_rate'] = ($adminData['matured_leads'] > 0 && $all_time_dsr_count > 0)
                ? round(($adminData['matured_leads'] / $all_time_dsr_count) * 100, 3)
                : 0;
        }

        // 2. Active Leads List (Filtered by selected year)
        $adminData['my_active_leads'] = Clients::with(['telereferral', 'referral', 'histories', 'history' => function ($q) use ($user) {
            $q->where('created', $user->id)->latest();
        }])
            ->where('ref_user', $user->id)
            ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
            ->whereYear('created_at', $selectedYear)
            ->latest()
            ->take(15)
            ->get();

        // 🔮 Real-Time Heuristic AI Prediction & Analysis Loader
        foreach ($adminData['my_active_leads'] as $lead) {
            $score = 15; // default base score
            $recommendation = "Engage the client with an introductory product demonstration.";
            $sentiment = "Neutral";

            if ($lead->status === 'Followup') {
                $score = 55;
                $recommendation = "Present dynamic corporate portfolio options and offer package customization.";
            } elseif ($lead->status === 'Meeting Fixed') {
                $score = 85;
                $recommendation = "Confirm meeting particulars via WhatsApp; prepare draft commercial proposal.";
            }

            // Touchpoints frequency modifier
            $touchpoints = $lead->histories ? $lead->histories->count() : 0;
            $score += min($touchpoints * 4, 20); // Up to +20 based on touchpoints

            // Remark scanning modifier
            $latestRemark = $lead->history ? strtolower($lead->history->remarks) : '';
            if ($latestRemark) {
                if (str_contains($latestRemark, 'interested') || str_contains($latestRemark, 'good') || str_contains($latestRemark, 'positive') || str_contains($latestRemark, 'will join')) {
                    $score += 15;
                    $sentiment = "Highly Positive";
                } elseif (str_contains($latestRemark, 'price') || str_contains($latestRemark, 'cost') || str_contains($latestRemark, 'budget') || str_contains($latestRemark, 'expensive')) {
                    $score += 5;
                    $recommendation = "Acknowledge budget limits. Propose modular payment tiers or split onboarding options.";
                    $sentiment = "Hesitant (Price)";
                } elseif (str_contains($latestRemark, 'busy') || str_contains($latestRemark, 'later') || str_contains($latestRemark, 'next month') || str_contains($latestRemark, 'not now')) {
                    $score -= 10;
                    $recommendation = "Keep distance. Schedule a low-pressure automated check-in via email next week.";
                    $sentiment = "Hesitant (Timing)";
                }
            }

            $lead->ai_score = min($score, 98); // cap score at 98%
            $lead->ai_recommendation = $recommendation;
            $lead->ai_sentiment = $sentiment;
        }

        // 3. Recently Matured Leads (Filtered by selected year)
        $adminData['recently_matured_leads'] = Clients::with(['telereferral', 'referral'])
            ->where('ref_user', $user->id)
            ->where('status', 'Matured')
            ->whereYear('updated_at', $selectedYear)
            ->latest('updated_at')
            ->take(8)
            ->get();

        // 4. Today's Scheduled Callbacks List (Top Level Live Cards)
        $adminData['todays_callbacks'] = Clients::where('ref_user', $user->id)
            ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
            ->whereHas('histories', function ($q) use ($user) {
                $q->where('tbro', Carbon::today()->toDateString());
                $q->where('created', $user->id);
            })
            ->with(['telereferral', 'referral', 'history' => function ($q) use ($user) {
                $q->where('created', $user->id)->latest();
            }])
            ->get();

        // 5. Stage Distribution for Chart
        $statusDistribution = [
            'Fresh' => Clients::where('ref_user', $user->id)->where('status', 'Fresh')->whereYear('created_at', $selectedYear)->count(),
            'Followup' => Clients::where('ref_user', $user->id)->where('status', 'Followup')->whereYear('created_at', $selectedYear)->count(),
            'Meeting Fixed' => Clients::where('ref_user', $user->id)->where('status', 'Meeting Fixed')->whereYear('created_at', $selectedYear)->count(),
            'Matured' => Clients::where('ref_user', $user->id)->where('status', 'Matured')->whereYear('updated_at', $selectedYear)->count(),
            'Not Interested' => Clients::where('ref_user', $user->id)->where('status', 'Not Interested')->whereYear('updated_at', $selectedYear)->count(),
        ];
        $adminData['status_distribution'] = $statusDistribution;

        // 6. Year select configurations
        $startYear = $user->created_at ? $user->created_at->year : date('Y');
        if ($startYear > (int)date('Y') - 3) {
            $startYear = (int)date('Y') - 3; // Ensure at least 3 historical years
        }
        $adminData['available_years'] = range((int)date('Y'), $startYear);
        $adminData['selected_year'] = (int)$selectedYear;

        return $adminData;
    }
}
