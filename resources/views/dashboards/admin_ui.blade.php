{{-- Executive Admin Dashboard UI --}}
<style>
    .wms-dash-card {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.035);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .wms-dash-card:hover {
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07);
        border-color: #e2e8f0;
    }

    .wms-kpi-card {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.035);
        transition: all 0.25s ease;
        cursor: pointer;
        display: block;
        color: inherit;
        text-decoration: none !important;
    }

    .wms-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07);
        border-color: #cbd5e1;
    }

    .wms-kpi-icon-wrap {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .wms-alert-box {
        border-radius: 14px;
        border: 1px solid #edf2f7;
        padding: 16px 18px;
        background: #ffffff;
        transition: all 0.2s ease;
    }

    .wms-alert-box:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .wms-priority-item {
        padding: 14px 18px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
        transition: all 0.2s ease;
    }

    .wms-priority-item:hover {
        background: #f1f5f9;
        border-color: #e2e8f0;
    }

    .funnel-bar-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 9px;
        font-size: 12px;
    }

    .funnel-bar-fill {
        height: 24px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        padding-left: 12px;
        color: #ffffff;
        font-weight: 600;
        font-size: 11.5px;
    }

    .performer-tab-btn {
        border: none;
        background: transparent;
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .performer-tab-btn.active {
        background: #eef2ff;
        color: #4f46e5;
    }

    .wms-table-compact thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.5px;
        border-top: none;
        border-bottom: 1px solid #e2e8f0;
        padding: 11px 14px;
    }

    .wms-table-compact tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
        font-size: 12px;
    }

    .live-dot-pulse {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulse-green 1.8s infinite;
    }

    @keyframes pulse-green {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }

        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }
</style>

{{-- ══ Top Greeting & Date Range Filter ═════════════════════════════════════════ --}}
<div class="row mb-4 align-items-center">
    <div class="col-lg-6">
        <h3 class="font-weight-bold text-dark mb-1 font-size-22">
            @php
            $hour = date('H');
            $greeting = 'Good morning';
            if ($hour >= 12 && $hour < 17) $greeting='Good afternoon' ;
                elseif ($hour>= 17) $greeting = 'Good evening';
                @endphp
                {{ $greeting }}, {{ Auth::user()->name ?? 'Admin' }}! 👋
        </h3>
        <p class="text-muted font-size-13 mb-0">Here's what's happening across your organization today.</p>
    </div>
    <div class="col-lg-6 text-lg-right mt-3 mt-lg-0">
        <div class="d-inline-flex align-items-center">
            {{-- Date Range Dropdown with Presets --}}
            <div class="dropdown mr-2">
                <button class="btn btn-white border bg-white shadow-sm font-size-12 font-weight-semibold px-3 py-2 dropdown-toggle" type="button" id="dashboardDateDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 10px;">
                    <i class="mdi mdi-calendar-range mr-1.5 text-primary"></i> <span id="currentDateFilterLabel">{{ $adminData['filter_label'] ?? ('Today: ' . now()->format('d M Y')) }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 py-2" aria-labelledby="dashboardDateDropdown" style="border-radius: 12px; min-width: 230px;">
                    <h6 class="dropdown-header font-size-11 text-uppercase text-muted">Quick Date Filters</h6>
                    <a class="dropdown-item font-size-12 py-2 {{ ($adminData['selected_preset'] ?? 'today') === 'today' ? 'font-weight-bold text-primary bg-light' : '' }}" href="javascript:void(0);" onclick="applyDatePreset('today', 'Today')">
                        <i class="mdi mdi-calendar-today mr-2"></i> Today
                    </a>
                    <a class="dropdown-item font-size-12 py-2 {{ ($adminData['selected_preset'] ?? '') === 'yesterday' ? 'font-weight-bold text-primary bg-light' : '' }}" href="javascript:void(0);" onclick="applyDatePreset('yesterday', 'Yesterday')">
                        <i class="mdi mdi-history mr-2"></i> Yesterday
                    </a>
                    <a class="dropdown-item font-size-12 py-2 {{ ($adminData['selected_preset'] ?? '') === 'this_week' ? 'font-weight-bold text-primary bg-light' : '' }}" href="javascript:void(0);" onclick="applyDatePreset('this_week', 'This Week')">
                        <i class="mdi mdi-calendar-week mr-2"></i> This Week
                    </a>
                    <a class="dropdown-item font-size-12 py-2 {{ ($adminData['selected_preset'] ?? '') === 'this_month' ? 'font-weight-bold text-primary bg-light' : '' }}" href="javascript:void(0);" onclick="applyDatePreset('this_month', 'This Month')">
                        <i class="mdi mdi-calendar-month mr-2"></i> This Month
                    </a>
                    <div class="dropdown-divider my-1"></div>
                    <div class="px-3 py-2">
                        <label class="font-size-11 text-muted font-weight-bold mb-1">Custom Date</label>
                        <input type="date" id="customDateInput" class="form-control form-control-sm font-size-11" value="{{ $adminData['custom_date_val'] ?? now()->format('Y-m-d') }}" onchange="applyCustomDate(this.value)">
                    </div>
                </div>
            </div>

            {{-- Staff Directory Quick Popup Button --}}
            <button type="button" class="btn btn-white border bg-white shadow-sm font-size-12 px-3 py-2 font-weight-bold" style="border-radius: 10px;" data-toggle="modal" data-target="#staffDirectoryModal">
                <i class="mdi mdi-account-group text-primary mr-1"></i> Staffs
            </button>

            {{-- 1-Click Live Refresh Button --}}
            <button type="button" class="btn btn-white border bg-white shadow-sm font-size-12 px-2.5 py-2 text-muted" style="border-radius: 10px;" title="Refresh Data" onclick="window.location.reload();">
                <i class="mdi mdi-refresh font-size-14"></i>
            </button>
        </div>
    </div>
</div>

{{-- ══ Row 1: Top 5 KPI Cards (With Deep Links & Sparklines) ═══════════════════ --}}
<div class="row mb-4">
    {{-- Card 1: Won Deals (MTD) --}}
    <div class="col-xl col-md-4 col-sm-6 mb-3 mb-xl-0">
        <a href="{{ route('clients.index') }}?status=Matured" class="wms-kpi-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-2.5">
                <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Won Deals (MTD)</span>
                <div class="wms-kpi-icon-wrap" style="background: #f5f3ff; color: #7c3aed;">
                    <i class="mdi mdi-trophy-outline"></i>
                </div>
            </div>
            <h3 class="font-weight-bold text-dark font-size-24 mb-1.5">{{ $adminData['won_deals_total'] }}</h3>
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-success font-weight-bold font-size-11">
                    <i class="mdi mdi-arrow-up font-size-12"></i> {{ $adminData['won_deals_growth_pct'] }}% <span class="text-muted font-weight-normal">vs last month</span>
                </span>
                <div id="sparkline-won-deals" style="width: 55px; height: 26px;"></div>
            </div>
        </a>
    </div>

    {{-- Card 2: Leads (Pipeline) --}}
    <div class="col-xl col-md-4 col-sm-6 mb-3 mb-xl-0">
        <a href="{{ route('clients.index') }}" class="wms-kpi-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-2.5">
                <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Leads</span>
                <div class="wms-kpi-icon-wrap" style="background: #ecfdf5; color: #059669;">
                    <i class="mdi mdi-account-multiple-plus-outline"></i>
                </div>
            </div>
            <h3 class="font-weight-bold text-dark font-size-24 mb-1.5">{{ $adminData['leads_total'] }}</h3>
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-success font-weight-bold font-size-11">
                    <i class="mdi mdi-arrow-up font-size-12"></i> {{ $adminData['leads_growth_pct'] }}% <span class="text-muted font-weight-normal">vs last month</span>
                </span>
                <div id="sparkline-leads" style="width: 55px; height: 26px;"></div>
            </div>
        </a>
    </div>

    {{-- Card 3: Active Projects --}}
    <div class="col-xl col-md-4 col-sm-6 mb-3 mb-xl-0">
        <a href="{{ url('projects?status=InProgress') }}" class="wms-kpi-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-2.5">
                <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Active Projects</span>
                <div class="wms-kpi-icon-wrap" style="background: #eff6ff; color: #2563eb;">
                    <i class="mdi mdi-briefcase-outline"></i>
                </div>
            </div>
            <h3 class="font-weight-bold text-dark font-size-24 mb-1.5">{{ $adminData['active_projects_count'] }}</h3>
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-{{ $adminData['overdue_projects_count'] > 0 ? 'danger' : 'muted' }} font-weight-bold font-size-11">
                    {{ $adminData['overdue_projects_count'] }} Overdue
                </span>
                <div id="sparkline-projects" style="width: 55px; height: 26px;"></div>
            </div>
        </a>
    </div>

    {{-- Card 4: Customers --}}
    <div class="col-xl col-md-4 col-sm-6 mb-3 mb-xl-0">
        <a href="{{ route('csd.clients.index') }}" class="wms-kpi-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-2.5">
                <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Customers</span>
                <div class="wms-kpi-icon-wrap" style="background: #fff7ed; color: #ea580c;">
                    <i class="mdi mdi-account-heart-outline"></i>
                </div>
            </div>
            <h3 class="font-weight-bold text-dark font-size-24 mb-1.5">{{ $adminData['customers_count'] }}</h3>
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-{{ $adminData['customers_at_risk_count'] > 0 ? 'danger' : 'success' }} font-weight-bold font-size-11">
                    {{ $adminData['customers_at_risk_count'] }} At Risk
                </span>
                <div id="sparkline-customers" style="width: 55px; height: 26px;"></div>
            </div>
        </a>
    </div>

    {{-- Card 5: Employees --}}
    <div class="col-xl col-md-4 col-sm-6">
        <a href="{{ route('admin.attendances.index') }}" class="wms-kpi-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-2.5">
                <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Employees</span>
                <div class="wms-kpi-icon-wrap" style="background: #eef2ff; color: #4f46e5;">
                    <i class="mdi mdi-account-outline"></i>
                </div>
            </div>
            <h3 class="font-weight-bold text-dark font-size-24 mb-1.5">{{ $adminData['employees_count'] }}</h3>
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-primary font-weight-bold font-size-11">
                    {{ $adminData['present_today_pct'] }}% Present Today
                </span>
                <div id="sparkline-employees" style="width: 55px; height: 26px;"></div>
            </div>
        </a>
    </div>
</div>

{{-- ══ Row 2: Action Panels (Needs Your Attention + Today's Priorities) ═════════ --}}
<div class="row mb-4">
    {{-- Left: Needs Your Attention --}}
    <div class="col-lg-7 mb-3 mb-lg-0">
        <div class="wms-dash-card h-100">
            <div class="d-flex align-items-center mb-3">
                <i class="mdi mdi-alert-circle text-danger mr-1.5 font-size-18"></i>
                <h5 class="text-dark font-size-14 font-weight-bold mb-0 text-uppercase" style="letter-spacing: 0.5px;">Needs Your Attention</h5>
            </div>
            <div class="row">
                {{-- 1. Projects Overdue --}}
                <div class="col-sm-6 col-md-3 mb-2 mb-md-0">
                    <div class="wms-alert-box h-100">
                        <div class="d-flex align-items-center mb-1.5">
                            <div class="avatar-xs rounded-circle bg-soft-danger text-danger d-flex align-items-center justify-content-center mr-2 font-weight-bold" style="width: 28px; height: 28px;">
                                <i class="mdi mdi-alert-octagon"></i>
                            </div>
                            <h6 class="mb-0 font-weight-bold text-dark font-size-14">{{ $adminData['overdue_projects_count'] }}</h6>
                        </div>
                        <span class="font-size-11 text-muted d-block mb-1.5">Projects overdue</span>
                        <a href="{{ url('projects?status=InProgress') }}" class="font-size-11 text-primary font-weight-bold">View Projects &rsaquo;</a>
                    </div>
                </div>

                {{-- 2. Leads No Follow-up --}}
                <div class="col-sm-6 col-md-3 mb-2 mb-md-0">
                    <div class="wms-alert-box h-100">
                        <div class="d-flex align-items-center mb-1.5">
                            <div class="avatar-xs rounded-circle bg-soft-warning text-warning d-flex align-items-center justify-content-center mr-2 font-weight-bold" style="width: 28px; height: 28px;">
                                <i class="mdi mdi-clock-alert-outline"></i>
                            </div>
                            <h6 class="mb-0 font-weight-bold text-dark font-size-14">{{ $adminData['leads_no_followup_count'] }}</h6>
                        </div>
                        <span class="font-size-11 text-muted d-block mb-1.5">Leads no follow-up</span>
                        <a href="{{ route('clients.index') }}?status=Fresh" class="font-size-11 text-primary font-weight-bold">View Leads &rsaquo;</a>
                    </div>
                </div>

                {{-- 3. Customer At Risk --}}
                <div class="col-sm-6 col-md-3 mb-2 mb-md-0">
                    <div class="wms-alert-box h-100">
                        <div class="d-flex align-items-center mb-1.5">
                            <div class="avatar-xs rounded-circle bg-soft-warning text-warning d-flex align-items-center justify-content-center mr-2 font-weight-bold" style="width: 28px; height: 28px;">
                                <i class="mdi mdi-alert-decagram-outline"></i>
                            </div>
                            <h6 class="mb-0 font-weight-bold text-dark font-size-14">{{ $adminData['customers_at_risk_count'] }}</h6>
                        </div>
                        <span class="font-size-11 text-muted d-block mb-1.5">Customer at risk</span>
                        <a href="{{ route('csd.clients.index') }}?health=at_risk" class="font-size-11 text-primary font-weight-bold">View Customer &rsaquo;</a>
                    </div>
                </div>

                {{-- 4. Employees Pending Closing --}}
                <div class="col-sm-6 col-md-3">
                    <div class="wms-alert-box h-100">
                        <div class="d-flex align-items-center mb-1.5">
                            <div class="avatar-xs rounded-circle bg-soft-warning text-warning d-flex align-items-center justify-content-center mr-2 font-weight-bold" style="width: 28px; height: 28px;">
                                <i class="mdi mdi-account-clock-outline"></i>
                            </div>
                            <h6 class="mb-0 font-weight-bold text-dark font-size-14">{{ $adminData['pending_closing_users_count'] }}</h6>
                        </div>
                        <span class="font-size-11 text-muted d-block mb-1.5">Pending closing</span>
                        <a href="{{ route('day-closing.approvals') }}" class="font-size-11 text-primary font-weight-bold">View Employees &rsaquo;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Today's Priorities --}}
    <div class="col-lg-5">
        <div class="wms-dash-card h-100">
            <div class="d-flex align-items-center mb-3">
                <i class="mdi mdi-clipboard-text-outline text-primary mr-1.5 font-size-18"></i>
                <h5 class="text-dark font-size-14 font-weight-bold mb-0 text-uppercase" style="letter-spacing: 0.5px;">Today's Priorities</h5>
            </div>
            <div class="row">
                {{-- 1. Day Closings --}}
                <div class="col-sm-6 mb-3">
                    <div class="wms-priority-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-calendar-check-outline font-size-18 text-muted mr-2"></i>
                            <span class="font-size-12 font-weight-semibold text-dark">{{ $adminData['day_closings_count'] }} Day Closings</span>
                        </div>
                        <a href="{{ route('day-closing.approvals') }}" class="btn btn-xs btn-light border font-size-11 font-weight-bold px-2.5 py-1 rounded">Review</a>
                    </div>
                </div>

                {{-- 2. Closing Approvals --}}
                <div class="col-sm-6 mb-3">
                    <div class="wms-priority-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-checkbox-marked-circle-outline font-size-18 text-muted mr-2"></i>
                            <span class="font-size-12 font-weight-semibold text-dark">{{ $adminData['closing_approvals_count'] }} Closing Approvals</span>
                        </div>
                        <a href="{{ route('day-closing.approvals') }}" class="btn btn-xs btn-light border font-size-11 font-weight-bold px-2.5 py-1 rounded">Review</a>
                    </div>
                </div>

                {{-- 3. Leave Approvals --}}
                <div class="col-sm-6 mb-2 mb-sm-0">
                    <div class="wms-priority-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-account-arrow-right-outline font-size-18 text-muted mr-2"></i>
                            <span class="font-size-12 font-weight-semibold text-dark">{{ $adminData['leave_approvals_count'] }} Leave Approvals</span>
                        </div>
                        <a href="{{ route('hrms.my-leaves.approvals') }}" class="btn btn-xs btn-light border font-size-11 font-weight-bold px-2.5 py-1 rounded">Review</a>
                    </div>
                </div>

                {{-- 4. Project Deadlines --}}
                <div class="col-sm-6">
                    <div class="wms-priority-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-clock-alert-outline font-size-18 text-muted mr-2"></i>
                            <span class="font-size-12 font-weight-semibold text-dark">{{ $adminData['project_deadlines_count'] }} Project Deadline</span>
                        </div>
                        <a href="{{ url('projects?status=InProgress') }}" class="btn btn-xs btn-light border font-size-11 font-weight-bold px-2.5 py-1 rounded">View</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ Row 3: 3-Department Overview Grid (NSD, OD, CSD) ═════════════════════════ --}}
<div class="row mb-4">
    {{-- 1. NSD (Sales) Overview --}}
    <div class="col-xl-4 col-lg-6 mb-3 mb-xl-0">
        <div class="wms-dash-card h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex align-items-center justify-content-between mb-3.5">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-trending-up text-primary mr-1.5 font-size-18"></i>
                        <h5 class="text-dark font-size-14 font-weight-bold mb-0 text-uppercase" style="letter-spacing: 0.5px;">NSD (Sales) Overview</h5>
                    </div>
                    <a href="{{ route('clients.index') }}" class="font-size-11 text-primary font-weight-semibold">View Sales &rsaquo;</a>
                </div>

                {{-- Mini Stats --}}
                <div class="row mb-3.5 mt-3 text-center">
                    <div class="col-3 border-right px-1">
                        <span class="text-muted font-size-11 d-block">Leads</span>
                        <h6 class="font-weight-bold text-dark font-size-15 my-0.5">{{ $adminData['nsd_leads'] }}</h6>
                        <small class="text-success font-weight-bold font-size-10">&uarr; 14.2%</small>
                    </div>
                    <div class="col-3 border-right px-1">
                        <span class="text-muted font-size-11 d-block">Qualified</span>
                        <h6 class="font-weight-bold text-dark font-size-15 my-0.5">{{ $adminData['nsd_qualified'] }}</h6>
                        <small class="text-success font-weight-bold font-size-10">&uarr; 8.5%</small>
                    </div>
                    <div class="col-3 border-right px-1">
                        <span class="text-muted font-size-11 d-block">Won</span>
                        <h6 class="font-weight-bold text-dark font-size-15 my-0.5">{{ $adminData['nsd_won'] }}</h6>
                        <small class="text-success font-weight-bold font-size-10">&uarr; 21.3%</small>
                    </div>
                    <div class="col-3 px-1">
                        <span class="text-muted font-size-11 d-block">Conversion</span>
                        <h6 class="font-weight-bold text-dark font-size-15 my-0.5">{{ $adminData['nsd_conversion_pct'] }}%</h6>
                        <small class="text-success font-weight-bold font-size-10">&uarr; 4.2%</small>
                    </div>
                </div>

                <div class="row">
                    {{-- Funnel List --}}
                    <div class="col-6 pr-2 mt-4">
                        <span class="text-dark font-weight-bold font-size-12 mb-2 d-block">Sales Funnel</span>
                        <div class="funnel-bar-row">
                            <div class="funnel-bar-fill" style="width: 100%; background: #4f46e5;">New Leads</div>
                            <span class="font-weight-bold text-dark ml-1 font-size-11">{{ $adminData['funnel_new_leads'] }}</span>
                        </div>
                        <div class="funnel-bar-row">
                            <div class="funnel-bar-fill" style="width: 82%; background: #6366f1;">Contacted</div>
                            <span class="font-weight-bold text-dark ml-1 font-size-11">{{ $adminData['funnel_contacted'] }}</span>
                        </div>
                        <div class="funnel-bar-row">
                            <div class="funnel-bar-fill" style="width: 65%; background: #818cf8;">Qualified</div>
                            <span class="font-weight-bold text-dark ml-1 font-size-11">{{ $adminData['funnel_qualified'] }}</span>
                        </div>
                        <div class="funnel-bar-row">
                            <div class="funnel-bar-fill" style="width: 48%; background: #06b6d4;">Proposals</div>
                            <span class="font-weight-bold text-dark ml-1 font-size-11">{{ $adminData['funnel_proposals'] }}</span>
                        </div>
                        <div class="funnel-bar-row">
                            <div class="funnel-bar-fill" style="width: 32%; background: #10b981;">Won</div>
                            <span class="font-weight-bold text-dark ml-1 font-size-11">{{ $adminData['funnel_won'] }}</span>
                        </div>
                    </div>

                    {{-- Mini Sales Trend Line Chart --}}
                    <div class="col-6 pl-2 mt-4">
                        <span class="text-dark font-weight-bold font-size-12 mb-1 d-block">Sales Trend (Last 12M)</span>
                        <div id="chart-sales-trend-mini" style="min-height: 155px;"></div>
                    </div>
                </div>
            </div>

            <div class="mt-3 p-2 rounded-lg bg-light text-center font-size-11 font-weight-semibold text-muted border">
                🏆 Won Deals MTD: <strong class="text-dark">{{ $adminData['won_deals_total'] }} deals</strong> &bull; <span class="text-success font-weight-bold">+{{ $adminData['won_deals_growth_pct'] }}% vs last month</span>
            </div>
        </div>
    </div>

    {{-- 2. OD (Operations) Overview --}}
    <div class="col-xl-4 col-lg-6 mb-3 mb-xl-0">
        <div class="wms-dash-card h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex align-items-center justify-content-between mb-3.5">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-cog-outline text-info mr-1.5 font-size-18"></i>
                        <h5 class="text-dark font-size-14 font-weight-bold mb-0 text-uppercase" style="letter-spacing: 0.5px;">OD (Operations) Overview</h5>
                    </div>
                    <a href="{{ url('projects') }}" class="font-size-11 text-primary font-weight-semibold">View Projects &rsaquo;</a>
                </div>

                {{-- Center Donut & Legend --}}
                <div class="d-flex align-items-center justify-content-between mb-3 px-2">
                    <div id="chart-od-projects-donut" style="width: 130px; height: 130px;"></div>
                    <div class="d-flex flex-column font-size-12" style="gap: 10px;">
                        <div class="d-flex align-items-center">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; display: inline-block; margin-right: 8px;"></span>
                            <span class="text-dark font-weight-semibold mr-2">{{ $adminData['od_on_track'] }} On Track</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b; display: inline-block; margin-right: 8px;"></span>
                            <span class="text-dark font-weight-semibold mr-2">{{ $adminData['od_at_risk'] }} At Risk</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444; display: inline-block; margin-right: 8px;"></span>
                            <span class="text-dark font-weight-semibold mr-2">{{ $adminData['od_overdue'] }} Overdue</span>
                        </div>
                    </div>
                </div>

                {{-- Upcoming Deadlines (5 Items) --}}
                <div>
                    <span class="text-dark font-weight-bold font-size-12 mb-2 d-block">Upcoming Deadlines</span>
                    <div style="max-height: 180px; overflow-y: auto;">
                        @forelse($adminData['upcoming_deadlines'] as $proj)
                        <div class="mb-2.5">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div>
                                    <span class="font-size-11.5 font-weight-semibold text-dark">{{ Str::limit($proj->name, 22) }}</span>
                                    <small class="text-muted font-size-10 ml-1">({{ $proj->client }})</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-soft-{{ $proj->due_class }} font-size-10 px-2 py-0.5 rounded font-weight-bold mr-1.5">
                                        {{ $proj->due_text }}
                                    </span>
                                    <span class="font-size-11 font-weight-bold text-muted">{{ $proj->progress }}%</span>
                                </div>
                            </div>
                            <div class="progress" style="height: 5px; border-radius: 3px; background: #e2e8f0;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $proj->progress }}%;"></div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted font-size-11">No upcoming deadlines.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-3 p-2 rounded-lg bg-light text-center font-size-11 font-weight-semibold text-muted border">
                🎯 Total Active Workload: <strong class="text-dark">{{ $adminData['active_projects_count'] }} Projects</strong> &bull; <strong class="text-dark">{{ $adminData['total_tasks'] }} Tasks</strong>
            </div>
        </div>
    </div>

    {{-- 3. CSD (Customer Success) Overview --}}
    <div class="col-xl-4 col-lg-12">
        <div class="wms-dash-card h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex align-items-center justify-content-between mb-3.5">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-account-heart-outline text-success mr-1.5 font-size-18"></i>
                        <h5 class="text-dark font-size-14 font-weight-bold mb-0 text-uppercase" style="letter-spacing: 0.5px;">CSD (Customer Success) Overview</h5>
                    </div>
                    <a href="{{ route('csd.clients.index') }}" class="font-size-11 text-primary font-weight-semibold">View Customers &rsaquo;</a>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-sm-6 d-flex align-items-center justify-content-center">
                        <div id="chart-csd-health-donut" style="width: 130px; height: 130px;"></div>
                    </div>
                    <div class="col-sm-6 font-size-12 mt-2 mt-sm-0">
                        <div class="d-flex align-items-center mb-2.5">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; display: inline-block; margin-right: 8px;"></span>
                            <span class="text-dark font-weight-semibold">{{ $adminData['csd_healthy'] }} Healthy</span>
                        </div>
                        <div class="d-flex align-items-center mb-2.5">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b; display: inline-block; margin-right: 8px;"></span>
                            <span class="text-dark font-weight-semibold">{{ $adminData['csd_attention'] }} Attention</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444; display: inline-block; margin-right: 8px;"></span>
                            <span class="text-dark font-weight-semibold">{{ $adminData['csd_at_risk'] }} At Risk</span>
                        </div>
                    </div>
                </div>

                {{-- At-Risk Customers List (5 Items) --}}
                <div>
                    <span class="text-dark font-weight-bold font-size-12 mb-2 d-block">At Risk Customers</span>
                    <div style="max-height: 180px; overflow-y: auto;">
                        @forelse($adminData['at_risk_customers'] as $cust)
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div>
                                <span class="font-size-11.5 font-weight-bold text-dark d-block">{{ $cust->name }}</span>
                                <small class="text-muted font-size-10.5">{{ $cust->reason }}</small>
                            </div>
                            <span class="badge badge-soft-{{ $cust->risk_class }} font-size-10 px-2 py-0.5 rounded font-weight-bold">
                                {{ $cust->risk_level }}
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted font-size-11">No at-risk customers recorded.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-3 p-2 rounded-lg bg-light text-center font-size-11 font-weight-semibold text-muted border">
                🛡️ Portfolio Health: <strong class="text-success">{{ $adminData['csd_healthy'] }}</strong> of {{ $adminData['customers_count'] }} Accounts in Prime Condition
            </div>
        </div>
    </div>
</div>

{{-- ══ Row 4: Bottom Operational Triple-Panel ═══════════════════════════════════ --}}
<div class="row mb-4">
    {{-- 1. Employee Live Work & Attendance Status Today --}}
    <div class="col-xl-4 col-lg-6 mb-3 mb-xl-0">
        <div class="wms-dash-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center">
                    <span class="live-dot-pulse mr-2"></span>
                    <h5 class="text-dark font-size-14 font-weight-bold mb-0 text-uppercase" style="letter-spacing: 0.5px;">Employee Status Today</h5>
                </div>
                <a href="{{ route('admin.attendances.index') }}" class="font-size-11 text-primary font-weight-semibold">Live Monitor &rsaquo;</a>
            </div>

            {{-- Status Pill Counters --}}
            <div class="d-flex align-items-center justify-content-between mb-3 p-2.5 bg-light rounded-lg font-size-11">
                <div><span class="font-weight-bold text-dark">{{ $adminData['employees_count'] }}</span> <span class="text-muted">Total</span></div>
                <div><span class="text-success font-weight-bold">&bull; {{ $adminData['present_today_count'] }}</span> <span class="text-muted">Present</span></div>
                <div><span class="text-warning font-weight-bold">&bull; {{ $adminData['on_leave_today_count'] }}</span> <span class="text-muted">Leave</span></div>
                <div><span class="text-danger font-weight-bold">&bull; {{ $adminData['absent_today_count'] }}</span> <span class="text-muted">Absent</span></div>
            </div>

            {{-- Comprehensive Real-Time Attendance & Work Status Table --}}
            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                <table class="table wms-table-compact table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Current Activity</th>
                            <th>In / Late</th>
                            <th>Logged</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($adminData['employee_status_list'] as $emp)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center font-weight-bold text-muted bg-white border mr-2 flex-shrink-0" style="width: 26px; height: 26px; font-size: 10px;">
                                        {{ strtoupper(substr($emp->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-weight-semibold text-dark font-size-11.5 d-block">{{ $emp->name }}</span>
                                        <small class="text-muted font-size-10">{{ $emp->dept }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($emp->is_working_now)
                                <span class="badge badge-soft-success font-size-10 font-weight-semibold d-inline-flex align-items-center" title="{{ $emp->active_task }}">
                                    <i class="mdi mdi-play-circle text-success mr-1"></i> {{ Str::limit($emp->active_task, 15) }}
                                </span>
                                @elseif($emp->status == 'Present')
                                <span class="badge badge-soft-warning font-size-10 font-weight-semibold">
                                    <i class="mdi mdi-pause-circle mr-0.5"></i> Idle / Break
                                </span>
                                @elseif($emp->status == 'On Leave')
                                <span class="badge badge-soft-info font-size-10 font-weight-semibold">On Leave</span>
                                @else
                                <span class="badge badge-soft-danger font-size-10 font-weight-semibold">Absent</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-size-11 font-weight-semibold text-dark">{{ $emp->check_in }}</span>
                                <div class="d-flex align-items-center flex-wrap mt-0.5" style="gap: 3px;">
                                    @if($emp->punctuality)
                                    <span class="badge badge-soft-{{ $emp->punctuality == 'On Time' ? 'success' : 'warning' }} font-size-9 px-1 py-0.5" style="width: fit-content;">
                                        {{ $emp->punctuality }}
                                    </span>
                                    @endif
                                    @if($emp->work_location)
                                        @php
                                            $locClass = 'badge-location-office';
                                            $locIcon = 'mdi-office-building';
                                            $locShort = 'Office';
                                            if ($emp->work_location === 'Work from Home') {
                                                $locClass = 'badge-location-wfh';
                                                $locIcon = 'mdi-home-variant';
                                                $locShort = 'WFH';
                                            } elseif ($emp->work_location === 'Client Place') {
                                                $locClass = 'badge-location-client';
                                                $locIcon = 'mdi-briefcase';
                                                $locShort = 'Client';
                                            }
                                        @endphp
                                        <span class="badge {{ $locClass }} font-size-9 px-1 py-0.5" title="{{ $emp->work_location }}">
                                            <i class="mdi {{ $locIcon }} mr-0.5"></i>{{ $locShort }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="font-size-11 font-weight-bold text-dark">{{ $emp->task_hours }}h <small class="text-muted font-weight-normal">task</small></div>
                                <small class="text-muted font-size-10">{{ $emp->shift_hours }}h shift</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 text-center">
                <a href="{{ route('admin.attendances.index') }}" class="font-size-11.5 text-primary font-weight-semibold">View Live Workforce Dashboard &rarr;</a>
            </div>
        </div>
    </div>

    {{-- 2. Top Performers (Tabs for NSD, OD, CSD) --}}
    <div class="col-xl-4 col-lg-6 mb-3 mb-xl-0">
        <div class="wms-dash-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3.5">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-trophy text-warning mr-1.5 font-size-18"></i>
                    <h5 class="text-dark font-size-14 font-weight-bold mb-0 text-uppercase" style="letter-spacing: 0.5px;">Top Performers</h5>
                </div>
                <span class="badge badge-light border text-muted font-size-10 font-weight-semibold px-2 py-0.5 rounded">This Month</span>
            </div>

            {{-- Tabs --}}
            <div class="d-flex align-items-center p-1 bg-light rounded-lg mb-3.5">
                <button type="button" class="performer-tab-btn flex-grow-1 active" onclick="switchPerformerTab('nsd', this)">NSD (Sales)</button>
                <button type="button" class="performer-tab-btn flex-grow-1" onclick="switchPerformerTab('od', this)">OD (Projects)</button>
                <button type="button" class="performer-tab-btn flex-grow-1" onclick="switchPerformerTab('csd', this)">CSD (Customers)</button>
            </div>

            {{-- NSD Tab Content --}}
            <div id="performer-tab-nsd" class="performer-tab-pane" style="min-height: 255px;">
                @php
                $medals = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
                $maxDeals = $adminData['top_nsd_performers']->max('deals_count') ?: 1;
                $topNsd = $adminData['top_nsd_performers']->first();
                @endphp

                @if($topNsd && $topNsd->deals_count > 0)
                <div class="p-2 mb-3 rounded-lg d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border: 1px solid #ddd6fe;">
                    <div class="d-flex align-items-center">
                        <span class="font-size-16 mr-2">👑</span>
                        <div>
                            <span class="font-size-11 font-weight-bold text-dark d-block">Monthly MVP: {{ $topNsd->name }}</span>
                            <small class="text-muted font-size-10">{{ $topNsd->deals_count }} Deals Closed</small>
                        </div>
                    </div>
                    <span class="badge badge-primary font-size-10 px-2 py-0.5 rounded font-weight-bold">Top Closer</span>
                </div>
                @endif

                <div style="max-height: 200px; overflow-y: auto;">
                    @forelse($adminData['top_nsd_performers'] as $idx => $p)
                    @php
                    $dealPct = min(100, max(12, round(($p->deals_count / $maxDeals) * 100)));
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <div class="d-flex align-items-center">
                                <span class="mr-1.5 font-size-13">{{ $medals[$idx] ?? '#' . ($idx + 1) }}</span>
                                <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center font-weight-bold text-muted bg-white border mr-2" style="width: 22px; height: 22px; font-size: 9.5px;">
                                    {{ strtoupper(substr($p->name, 0, 2)) }}
                                </div>
                                <div>
                                    <span class="font-size-12 font-weight-bold text-dark d-block leading-tight">{{ $p->name }}</span>
                                    <small class="text-muted font-size-10">{{ $p->role_name ?? 'Sales Executive' }}</small>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-size-11.5 font-weight-bold text-success">{{ $p->deals_count }} Deals</span>
                                <small class="text-muted font-size-10 d-block">{{ $p->leads_count ?? 0 }} Leads</small>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 3px; background: #e2e8f0;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $dealPct }}%;"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted font-size-11">No sales records for this month yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- OD Tab Content (Tasks + Total Task Hours) --}}
            <div id="performer-tab-od" class="performer-tab-pane" style="display: none; min-height: 255px;">
                @php
                $maxTasks = $adminData['top_od_performers']->max('tasks_count') ?: 1;
                $topOd = $adminData['top_od_performers']->first();
                @endphp

                @if($topOd && ($topOd->tasks_count > 0 || ($topOd->formatted_hours ?? 0) > 0))
                <div class="p-2 mb-3 rounded-lg d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe;">
                    <div class="d-flex align-items-center">
                        <span class="font-size-16 mr-2">⚡</span>
                        <div>
                            <span class="font-size-11 font-weight-bold text-dark d-block">Tech Contributor: {{ $topOd->name }}</span>
                            <small class="text-muted font-size-10">{{ $topOd->formatted_hours ?? 0 }}h Logged • {{ $topOd->tasks_count }} Tasks</small>
                        </div>
                    </div>
                    <span class="badge badge-info font-size-10 px-2 py-0.5 rounded font-weight-bold">Top Velocity</span>
                </div>
                @endif

                <div style="max-height: 200px; overflow-y: auto;">
                    @forelse($adminData['top_od_performers'] as $idx => $p)
                    @php
                    $taskPct = min(100, max(12, round(($p->tasks_count / $maxTasks) * 100)));
                    $hours = $p->formatted_hours ?? round((float)($p->total_hours ?? 0), 1);
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <div class="d-flex align-items-center">
                                <span class="mr-1.5 font-size-13">{{ $medals[$idx] ?? '#' . ($idx + 1) }}</span>
                                <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center font-weight-bold text-muted bg-white border mr-2" style="width: 22px; height: 22px; font-size: 9.5px;">
                                    {{ strtoupper(substr($p->name, 0, 2)) }}
                                </div>
                                <div>
                                    <span class="font-size-12 font-weight-bold text-dark d-block leading-tight">{{ $p->name }}</span>
                                    <small class="text-muted font-size-10">{{ $p->role_name ?? 'Developer' }}</small>
                                </div>
                            </div>
                            <div class="text-right font-size-11 font-weight-bold">
                                <span class="text-primary">{{ $p->tasks_count }} Tasks</span>
                                <small class="text-muted font-size-10 d-block">{{ $hours }}h logged</small>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 3px; background: #e2e8f0;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $taskPct }}%;"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted font-size-11">No task records for this month yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- CSD Tab Content --}}
            <div id="performer-tab-csd" class="performer-tab-pane" style="display: none; min-height: 255px;">
                @php
                $maxRetentions = $adminData['top_csd_performers']->max('retentions_count') ?: 1;
                $topCsd = $adminData['top_csd_performers']->first();
                @endphp

                @if($topCsd && $topCsd->retentions_count > 0)
                <div class="p-2 mb-3 rounded-lg d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 1px solid #a7f3d0;">
                    <div class="d-flex align-items-center">
                        <span class="font-size-16 mr-2">🤝</span>
                        <div>
                            <span class="font-size-11 font-weight-bold text-dark d-block">Client Champion: {{ $topCsd->name }}</span>
                            <small class="text-muted font-size-10">{{ $topCsd->retentions_count }} Active Retainers</small>
                        </div>
                    </div>
                    <span class="badge badge-success font-size-10 px-2 py-0.5 rounded font-weight-bold">Top Retention</span>
                </div>
                @endif

                <div style="max-height: 200px; overflow-y: auto;">
                    @forelse($adminData['top_csd_performers'] as $idx => $p)
                    @php
                    $retPct = min(100, max(12, round(($p->retentions_count / $maxRetentions) * 100)));
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <div class="d-flex align-items-center">
                                <span class="mr-1.5 font-size-13">{{ $medals[$idx] ?? '#' . ($idx + 1) }}</span>
                                <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center font-weight-bold text-muted bg-white border mr-2" style="width: 22px; height: 22px; font-size: 9.5px;">
                                    {{ strtoupper(substr($p->name, 0, 2)) }}
                                </div>
                                <div>
                                    <span class="font-size-12 font-weight-bold text-dark d-block leading-tight">{{ $p->name }}</span>
                                    <small class="text-muted font-size-10">{{ $p->role_name ?? 'CSD Executive' }}</small>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-size-11.5 font-weight-bold text-teal">{{ $p->retentions_count }} Retainers</span>
                                <small class="text-muted font-size-10 d-block">100% Health</small>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 3px; background: #e2e8f0;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $retPct }}%;"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted font-size-11">No customer retainers recorded yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-3 text-center">
                <a href="{{ route('reports.employees') }}" class="font-size-11.5 text-primary font-weight-semibold">View Full Leaderboard &rarr;</a>
            </div>
        </div>
    </div>

    {{-- 3. Recent Activity Feed --}}
    <div class="col-xl-4 col-lg-12">
        <div class="wms-dash-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3.5">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-bell-ring-outline text-primary mr-1.5 font-size-18"></i>
                    <h5 class="text-dark font-size-14 font-weight-bold mb-0 text-uppercase" style="letter-spacing: 0.5px;">Recent Activity</h5>
                </div>
                <a href="javascript:void(0);" class="font-size-11 text-primary font-weight-semibold">View All &rsaquo;</a>
            </div>

            <div class="d-flex flex-column" style="gap: 14px;">
                @forelse($adminData['recent_activities'] as $act)
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center text-{{ $act->color }} mr-2.5 flex-shrink-0" style="width: 30px; height: 30px; background: {{ $act->bg }};">
                            <i class="mdi {{ $act->icon }} font-size-15"></i>
                        </div>
                        <span class="font-size-11.5 font-weight-semibold text-dark">{{ $act->text }}</span>
                    </div>
                    <small class="text-muted font-size-10.5 flex-shrink-0 ml-2">{{ $act->time }}</small>
                </div>
                @empty
                <div class="text-center py-4 text-muted font-size-11">No recent activity.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    function switchPerformerTab(type, el) {
        document.querySelectorAll('.performer-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.performer-tab-pane').forEach(pane => pane.style.display = 'none');
        el.classList.add('active');
        document.getElementById('performer-tab-' + type).style.display = 'block';
    }

    function applyDatePreset(preset, label) {
        document.getElementById('currentDateFilterLabel').innerText = label;
        // Trigger page refresh with preset param if needed
        if (preset !== 'today') {
            window.location.href = '{{ route("home") }}?preset=' + preset;
        } else {
            window.location.href = '{{ route("home") }}';
        }
    }

    function applyCustomDate(val) {
        if (val) {
            document.getElementById('currentDateFilterLabel').innerText = 'Date: ' + val;
            window.location.href = '{{ route("home") }}?date=' + val;
        }
    }

    // Live Auto-Refresh Admin Dashboard every 60 seconds
    setInterval(function() {
        if (!document.hidden && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            window.location.reload();
        }
    }, 60000);
</script>
