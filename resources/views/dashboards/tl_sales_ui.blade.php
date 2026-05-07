{{-- Sales Department Team Leader Oversight UI --}}
<style>
    /* Premium Visual Design Elements */
    .sales-tl-container {
        font-family: 'Outfit', 'Inter', sans-serif;
    }

    @keyframes card-entrance {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-entrance {
        animation: card-entrance 0.6s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
    }

    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }
    .delay-4 { animation-delay: 0.4s; }

    .tl-card-glass {
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid rgba(230, 230, 240, 0.7);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .tl-card-glass:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(100, 110, 220, 0.08);
        border-color: rgba(180, 190, 240, 0.6);
    }

    .tl-metric-card-green, .tl-metric-card-purple, .tl-metric-card-red, .tl-metric-card-blue {
        min-height: 132px;
        cursor: pointer;
    }

    .tl-metric-card-green {
        background: #ffffff;
        border: 1px solid rgba(17, 153, 142, 0.2) !important;
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .tl-metric-card-green:hover {
        transform: translateY(-6px) scale(1.02);
        border-color: rgba(17, 153, 142, 0.6) !important;
        box-shadow: 0 15px 30px rgba(17, 153, 142, 0.12) !important;
    }
    .tl-metric-card-green .card-header-text {
        color: #11998e !important;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .tl-metric-card-green .card-value-text {
        color: #11998e !important;
        font-weight: 700;
    }

    .tl-metric-card-purple {
        background: #ffffff;
        border: 1px solid rgba(127, 0, 255, 0.2) !important;
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .tl-metric-card-purple:hover {
        transform: translateY(-6px) scale(1.02);
        border-color: rgba(127, 0, 255, 0.6) !important;
        box-shadow: 0 15px 30px rgba(127, 0, 255, 0.12) !important;
    }
    .tl-metric-card-purple .card-header-text {
        color: #7F00FF !important;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .tl-metric-card-purple .card-value-text {
        color: #7F00FF !important;
        font-weight: 700;
    }

    .tl-metric-card-red {
        background: #ffffff;
        border: 1px solid rgba(255, 65, 108, 0.2) !important;
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .tl-metric-card-red:hover {
        transform: translateY(-6px) scale(1.02);
        border-color: rgba(255, 65, 108, 0.6) !important;
        box-shadow: 0 15px 30px rgba(255, 65, 108, 0.12) !important;
    }
    .tl-metric-card-red .card-header-text {
        color: #FF416C !important;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .tl-metric-card-red .card-value-text {
        color: #FF416C !important;
        font-weight: 700;
    }

    .tl-metric-card-blue {
        background: #ffffff;
        border: 1px solid rgba(0, 223, 254, 0.2) !important;
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .tl-metric-card-blue:hover {
        transform: translateY(-6px) scale(1.02);
        border-color: rgba(0, 223, 254, 0.6) !important;
        box-shadow: 0 15px 30px rgba(0, 223, 254, 0.12) !important;
    }
    .tl-metric-card-blue .card-header-text {
        color: #00B4DB !important;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .tl-metric-card-blue .card-value-text {
        color: #00B4DB !important;
        font-weight: 700;
    }

    .tl-metric-badge-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .tl-metric-badge-icon i {
        color: #ffffff !important;
        font-size: 1.5rem !important;
        line-height: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Pulse Dots */
    .active-dot-green {
        width: 8px;
        height: 8px;
        background-color: #34c38f;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
        box-shadow: 0 0 0 0 rgba(52, 195, 143, 0.4);
        animation: pulse-active-green 2s infinite;
    }

    @keyframes pulse-active-green {
        0% { box-shadow: 0 0 0 0 rgba(52, 195, 143, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(52, 195, 143, 0); }
        100% { box-shadow: 0 0 0 0 rgba(52, 195, 143, 0); }
    }

    /* Overload blinking crimson warning */
    @keyframes pulse-overload {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(244, 106, 106, 0.4); }
        70% { transform: scale(1.03); box-shadow: 0 0 0 6px rgba(244, 106, 106, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(244, 106, 106, 0); }
    }

    .overload-warning {
        animation: pulse-overload 1.5s infinite;
        border: 1px solid rgba(244, 106, 106, 0.4) !important;
    }

    .flame-shake {
        display: inline-block;
        animation: shake-flame 1s ease-in-out infinite alternate;
    }

    @keyframes shake-flame {
        0% { transform: rotate(-5deg); }
        100% { transform: rotate(5deg) scale(1.1); }
    }

    /* Name links */
    .client-name-link {
        color: #343a40;
        font-weight: 700;
        text-decoration: none;
        transition: color 0.25s ease;
    }
    .client-name-link:hover {
        color: #7F00FF;
        text-decoration: none;
    }
</style>

<div class="sales-tl-container">
    <!-- Row 1: Command Header -->
    <div class="row mb-4 align-items-center animate-entrance">
        <div class="col-sm-6">
            <div class="d-flex align-items-center">
                <div class="avatar-sm mr-3">
                    <span class="avatar-title rounded-circle shadow-lg" style="background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%);">
                        <i class="mdi mdi-shield-crown font-size-22 text-white"></i>
                    </span>
                </div>
                <div>
                    <h4 class="header-title mb-0" style="font-weight: 800; color: #1a1a1a; letter-spacing: -0.8px; font-size: 1.4rem;">
                        Sales Operations <span class="text-primary">Command</span>
                    </h4>
                    <div class="d-flex align-items-center mt-1">
                        <span class="active-dot-green"></span>
                        <span class="badge badge-soft-success font-size-10 mr-2">LIVE MONITORING</span>
                        <p class="text-muted mb-0 font-size-11 font-weight-medium">Team oversight & lead management console</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right d-flex align-items-center mt-3 mt-sm-0">
                <div class="mr-4 px-3 py-2 bg-white rounded shadow-sm border" style="border-left: 4px solid #7F00FF !important; border-radius: 12px !important;">
                    <p class="text-muted mb-0 font-size-10 text-uppercase letter-spacing-1 font-weight-bold">Operational Year</p>
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-calendar-range text-primary mr-2 font-size-16" style="color: #7F00FF !important;"></i>
                        <select class="form-control form-control-sm border-0 shadow-none p-0 font-size-15" id="sales_tl_dashboard_year_filter" style="font-weight: 800; color: #343a40; background: transparent; cursor: pointer; height: auto; width: 80px;">
                            @foreach($adminData['available_years'] as $yr)
                            <option value="{{ $yr }}" {{ $adminData['selected_year'] == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="text-right">
                    <span class="badge badge-primary p-2 shadow-sm" style="background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%); border-radius: 8px;">
                        <i class="mdi mdi-security mr-1"></i> TL Sales Command
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Team-Wide Aggregate KPI Pulse Cards -->
    <div class="row mb-4">
        <div class="col-md-3 animate-entrance delay-1">
            <div class="card tl-metric-card-green shadow-sm" onclick="window.location.href='{{ route('clients.category', ['category' => 'Matured']) }}'">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="card-header-text d-block mb-1">Team Matured Sales</span>
                        <h2 class="card-value-text mb-0 font-size-24">{{ $adminData['total_sales'] }}</h2>
                        <small class="text-muted">Contracts in {{ $adminData['selected_year'] }}</small>
                    </div>
                    <div class="tl-metric-badge-icon gradient-green-teal">
                        <i class="mdi mdi-handshake"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 animate-entrance delay-2">
            <div class="card tl-metric-card-blue shadow-sm" onclick="document.getElementById('team-performance-matrix-card').scrollIntoView({behavior: 'smooth'})">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="card-header-text d-block mb-1">Team Active Leads</span>
                        <h2 class="card-value-text mb-0 font-size-24">{{ $adminData['total_active_leads'] }}</h2>
                        <small class="text-muted">Total active pipeline</small>
                    </div>
                    <div class="tl-metric-badge-icon gradient-sky-blue">
                        <i class="mdi mdi-account-group-outline"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 animate-entrance delay-3">
            <div class="card tl-metric-card-purple shadow-sm" onclick="document.getElementById('todays-team-callbacks-card').scrollIntoView({behavior: 'smooth'})">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="card-header-text d-block mb-1">Callbacks Today</span>
                        <h2 class="card-value-text mb-0 font-size-24">{{ $adminData['todays_tbros_count'] }}</h2>
                        <small class="text-muted">Scheduled for today</small>
                    </div>
                    <div class="tl-metric-badge-icon gradient-purple-blue">
                        <i class="mdi mdi-phone-ring"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 animate-entrance delay-4">
            <div class="card tl-metric-card-red shadow-sm {{ $adminData['overdue_tbros_count'] > 0 ? 'overload-warning' : '' }}" onclick="document.getElementById('team-performance-matrix-card').scrollIntoView({behavior: 'smooth'})">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="card-header-text d-block mb-1">Overdue Callbacks</span>
                        <h2 class="card-value-text mb-0 font-size-24 text-danger">
                            {{ $adminData['overdue_tbros_count'] }}
                            @if($adminData['overdue_tbros_count'] > 0)
                            <span class="flame-shake">🔥</span>
                            @endif
                        </h2>
                        <small class="text-muted">Immediate action needed</small>
                    </div>
                    <div class="tl-metric-badge-icon gradient-orange-red">
                        <i class="mdi mdi-alert-circle-outline"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Your Team: Active Work Summary & Team Stage Conversion Distribution -->
    <div class="row mb-4">
        <!-- Team Oversight Grid -->
        <div class="col-lg-8 animate-entrance delay-1">
            <div class="card tl-card-glass h-100 border-0" id="team-performance-matrix-card">
                <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="font-size-15 mb-0 text-dark font-weight-bold">
                        <i class="mdi mdi-account-multiple-outline mr-1 text-primary"></i> Your Sales Force: Performance Matrix
                    </h5>
                    <span class="badge badge-soft-primary px-2 font-weight-bold">{{ count($adminData['team_performance_matrix'] ?? []) }} Executives</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-centered table-nowrap mb-0 trendy-table">
                            <thead class="thead-light">
                                <tr class="small text-uppercase">
                                    <th class="pl-4">Executive Name</th>
                                    <th class="text-center">Active Leads</th>
                                    <th class="text-center">Matured Sales</th>
                                    <th class="text-center">Callbacks Today</th>
                                    <th class="text-center">Overdue Callbacks</th>
                                    <th class="text-center">Workload State</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adminData['team_performance_matrix'] ?? [] as $emp)
                                @php
                                    $activeCount = $emp->active_leads_count;
                                    $overdueCount = $emp->overdue_callbacks_count;

                                    // Workload analysis logic
                                    if ($activeCount >= 5) {
                                        $workloadLabel = 'Overloaded';
                                        $workloadBadge = 'badge-soft-danger';
                                        $hasFlame = true;
                                    } elseif ($activeCount >= 3) {
                                        $workloadLabel = 'Busy';
                                        $workloadBadge = 'badge-soft-warning';
                                        $hasFlame = false;
                                    } else {
                                        $workloadLabel = 'Optimal';
                                        $workloadBadge = 'badge-soft-success';
                                        $hasFlame = false;
                                    }
                                @endphp
                                <tr>
                                    <td class="pl-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ Avatar::create($emp->name)->toBase64() }}" class="rounded-circle mr-2 shadow-sm" style="width: 32px; height: 32px; border: 2px solid {{ $activeCount >= 5 ? '#ea5455' : 'transparent' }};">
                                            <div>
                                                <span class="font-size-13 font-weight-bold d-block text-dark">{{ $emp->name }}</span>
                                                <small class="text-muted">ID: #{{ $emp->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center font-weight-bold text-dark">{{ $activeCount }}</td>
                                    <td class="text-center font-weight-bold text-success">{{ $emp->matured_leads_count }}</td>
                                    <td class="text-center font-weight-bold text-purple" style="color: #7F00FF;">{{ $emp->today_callbacks_count }}</td>
                                    <td class="text-center font-weight-bold text-danger">
                                        {{ $overdueCount }}
                                        @if($overdueCount > 0)
                                        <span class="badge badge-danger p-1 ml-1" style="border-radius: 50%; width: 6px; height: 6px; display: inline-block;"></span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $workloadBadge }} font-size-10 font-weight-bold px-2 py-1">
                                            {{ $workloadLabel }}
                                            @if($hasFlame)
                                            <span class="flame-shake ml-1">🔥</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-soft-primary btn-rounded px-3 nudge-executive-btn" data-exec-id="{{ $emp->id }}" data-exec-name="{{ $emp->name }}" style="border-radius: 20px;">
                                            <i class="mdi mdi-bullhorn-outline mr-1"></i> Nudge
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No team members assigned.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Conversion Distribution Apex Chart -->
        <div class="col-lg-4 animate-entrance delay-2">
            <div class="card tl-card-glass h-100 border-0">
                <div class="card-body d-flex flex-column">
                    <h5 class="font-size-15 mb-3 text-dark font-weight-bold">
                        <i class="mdi mdi-chart-donut-variant mr-1 text-primary"></i> Team Conversion Ratio
                    </h5>
                    
                    <div id="sales-team-distribution-donut" class="apex-charts flex-grow-1" style="min-height: 250px;" dir="ltr"></div>
                    
                    <div class="mt-auto border-top pt-3">
                        <div class="row text-center small">
                            <div class="col-4">
                                <p class="text-muted mb-1">Matured</p>
                                <h5 class="mb-0 text-success font-weight-bold">{{ $adminData['status_distribution']['Matured'] }}</h5>
                            </div>
                            <div class="col-4">
                                <p class="text-muted mb-1">Callbacks</p>
                                <h5 class="mb-0 text-primary font-weight-bold">{{ $adminData['status_distribution']['Followup'] + $adminData['status_distribution']['Meeting Fixed'] }}</h5>
                            </div>
                            <div class="col-4">
                                <p class="text-muted mb-1">Closed/Junk</p>
                                <h5 class="mb-0 text-danger font-weight-bold">{{ $adminData['status_distribution']['Not Interested'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 4: Lead Allocation command & Priority Team Callbacks -->
    <div class="row mb-5">
        <!-- Lead Allocation Control Panel -->
        <div class="col-lg-6 animate-entrance delay-3">
            <div class="card tl-card-glass h-100 border-0">
                <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="font-size-15 mb-0 text-dark font-weight-bold">
                        <i class="mdi mdi-account-arrow-right-outline mr-1 text-primary"></i> Lead Allocation Panel
                    </h5>
                    <span class="badge badge-soft-info font-weight-bold px-2 py-1">{{ count($adminData['unassigned_fresh_leads'] ?? []) }} Unassigned</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-centered table-nowrap mb-0 trendy-table">
                            <thead class="thead-light">
                                <tr class="small text-uppercase">
                                    <th style="width: 43%;">Lead Details</th>
                                    <th style="width: 20%;">Created By</th>
                                    <th style="width: 22%;">Select Executive</th>
                                    <th class="text-center" style="width: 15%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adminData['unassigned_fresh_leads'] ?? [] as $lead)
                                <tr id="alloc-row-{{ $lead->id }}">
                                    <td style="max-width: 200px; overflow: hidden;">
                                        <a href="{{ url('clients/' . base64_encode($lead->id) . '/sts') }}" class="client-name-link font-weight-bold d-block text-truncate" title="{{ $lead->name }}">
                                            {{ $lead->name }}
                                        </a>
                                        <small class="text-muted text-truncate d-block" title="{{ $lead->cont_person }} | {{ $lead->mobile }}">{{ $lead->cont_person }} | {{ $lead->mobile }}</small>
                                    </td>
                                    <td>
                                        <span class="font-size-12 font-weight-bold d-block text-dark text-truncate" title="{{ $lead->creator->name ?? 'System' }}">
                                            <i class="mdi mdi-account-circle-outline mr-1 text-muted"></i>
                                            {{ explode(' ', $lead->creator->name ?? 'System')[0] }}
                                        </span>
                                        <small class="text-muted">
                                            <i class="mdi mdi-calendar-clock mr-1"></i>
                                            {{ $lead->created_at ? $lead->created_at->format('d M') : 'N/A' }}
                                        </small>
                                    </td>
                                    <td>
                                        <select class="form-control form-control-sm select-allocation-executive" data-lead-id="{{ $lead->id }}" style="border-radius: 8px; border: 1px solid rgba(200, 200, 220, 0.7); height: 35px;">
                                            <option value="">-- Choose Exec --</option>
                                            @foreach($adminData['allocatable_team_members'] ?? [] as $m)
                                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary px-3 allocate-lead-btn" data-lead-id="{{ $lead->id }}" style="border-radius: 12px; background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%); border: none;">
                                            Assign
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">All fresh leads allocated. Beautiful job! 🌟</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's priority team callbacks queue -->
        <div class="col-lg-6 animate-entrance delay-4">
            <div class="card tl-card-glass h-100 border-0" id="todays-team-callbacks-card">
                <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="font-size-15 mb-0 text-dark font-weight-bold">
                        <i class="mdi mdi-clock-check-outline mr-1 text-primary"></i> Team Callbacks Queue (Today)
                    </h5>
                    <span class="badge badge-soft-purple font-weight-bold px-2 py-1">{{ count($adminData['todays_callbacks'] ?? []) }} Due</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-centered table-nowrap mb-0 trendy-table">
                            <thead class="thead-light">
                                <tr class="small text-uppercase">
                                    <th>Client Name</th>
                                    <th>Assigned Executive</th>
                                    <th>Schedule Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adminData['todays_callbacks'] ?? [] as $callback)
                                @php
                                    // 1. Find the scheduled history entry for today's date
                                    $scheduledEntry = $callback->histories->first(function($h) {
                                        return $h->tbro == \Carbon\Carbon::today()->toDateString();
                                    });

                                    // 2. Find any newer history entry created today that is newer than the scheduling entry
                                    $followupEntry = null;
                                    if ($scheduledEntry) {
                                        $followupEntry = $callback->histories->first(function($h) use ($scheduledEntry) {
                                            return $h->id > $scheduledEntry->id && $h->created_at->isToday();
                                        });
                                    } else {
                                        // Fallback: check if any history was created today
                                        $followupEntry = $callback->histories->first(function($h) {
                                            return $h->created_at->isToday() && $h->tbro != \Carbon\Carbon::today()->toDateString();
                                        });
                                    }
                                @endphp
                                <tr style="{{ $followupEntry ? 'background-color: rgba(52, 195, 143, 0.03);' : '' }}">
                                    <td>
                                        <a href="{{ url('clients/' . base64_encode($callback->id) . '/sts') }}" class="client-name-link font-weight-bold">
                                            {{ $callback->name }}
                                        </a>
                                        @if($followupEntry)
                                            <span class="d-block text-success font-size-11 font-weight-bold mt-1">
                                                <i class="mdi mdi-checkbox-marked-circle-outline mr-1"></i> Followed Up
                                            </span>
                                        @else
                                            <span class="d-block text-warning font-size-11 font-weight-bold mt-1">
                                                <i class="mdi mdi-clock-outline mr-1"></i> Pending Callback
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-soft-info px-2 py-1 font-weight-bold">{{ $callback->referral->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($followupEntry)
                                            <span class="badge badge-soft-success font-weight-bold px-2 py-1" style="font-size: 11px;">
                                                <i class="mdi mdi-calendar-check mr-1"></i>
                                                {{ $followupEntry->created_at ? $followupEntry->created_at->format('h:i A') : ($followupEntry->time ?? 'N/A') }}
                                            </span>
                                        @else
                                            <span class="text-primary font-weight-bold small">
                                                <i class="mdi mdi-clock-outline mr-1"></i>
                                                {{ $scheduledEntry && $scheduledEntry->time ? \Carbon\Carbon::parse($scheduledEntry->time)->format('h:i A') : 'N/A' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($followupEntry)
                                            <span class="badge badge-success px-3 py-1 font-weight-bold text-white shadow-sm" style="border-radius: 20px; background-color: #34c38f; font-size: 11px;">
                                                <i class="mdi mdi-check-all mr-1"></i> Completed
                                            </span>
                                        @else
                                            <button class="btn btn-sm btn-soft-danger px-2 py-1 nudge-lead-specific-btn" data-lead-id="{{ $callback->id }}" data-lead-name="{{ $callback->name }}" data-exec-name="{{ $callback->referral->name ?? 'Executive' }}" style="border-radius: 12px; font-size: 11px;">
                                                <i class="mdi mdi-bell-ring-outline"></i> Nudge
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No scheduled callbacks for your team today.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
