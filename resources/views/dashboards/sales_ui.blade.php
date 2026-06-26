{{-- Sales Executive / Team Leader Dept 1 Premium UI --}}
<script>
    window.salesStatusDistribution = @json($adminData['status_distribution'] ?? []);
</script>

<div class="sales-dashboard-container">

    <!-- 🗓 Top Header with dynamic Welcome greeting and Dynamic Year Selection dropdown -->
    <div class="card mb-4 sales-card-glass" style="background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(245,247,255,0.95) 100%); border-left: 5px solid #7F00FF;">
        <div class="card-body py-3 px-4">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h3 class="text-premium-dark font-size-20 mb-1">Welcome back, {{ $user->name }}! 👋</h3>
                    <p class="text-muted font-size-13 mb-0">Your specialized Sales Co-Pilot workspace is operational, analyzing lead touchpoints and highlighting deal maturities.</p>
                </div>
                <div class="col-md-5 text-md-right mt-3 mt-md-0 d-flex align-items-center justify-content-md-end style-gap-12" style="gap: 12px; flex-wrap: wrap;">
                    <a href="{{ url('clients/create') }}" class="btn btn-primary d-inline-flex align-items-center font-weight-bold" style="border-radius: 12px; height: 44px; padding: 0 16px; background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%); border: none; box-shadow: 0 4px 15px rgba(127,0,255,0.25); font-size: 13px;">
                        <i class="mdi mdi-plus-circle font-size-16 mr-1"></i> Add New Company
                    </a>

                    <div class="d-inline-flex align-items-center bg-white p-2" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.06); height: 44px;">
                        <span class="text-premium-muted mr-2 font-size-10 font-weight-bold" style="letter-spacing: 0.5px; white-space: nowrap;"><i class="bx bx-calendar-event"></i> YEAR:</span>
                        <select class="form-control d-inline-block font-size-13 text-premium-dark select-year-filter-trigger" style="width: 100px; border-radius: 8px; border-color: rgba(127, 0, 255, 0.2); font-weight: 700; height: 28px; padding: 2px 8px; border: none; background: transparent;">
                            @foreach($adminData['available_years'] ?? [date('Y')] as $yr)
                            <option value="{{ $yr }}" {{ (isset($adminData['selected_year']) && $adminData['selected_year'] == $yr) ? 'selected' : '' }}>
                                🗓 {{ $yr }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($user->hasRole('Team-Leader') && empty($forceExecutiveView))
    {{-- =========================================================================
         👥 SALES TEAM LEADER DASHBOARD VIEW
         ========================================================================= --}}

    <!-- Top KPI Grid -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <a href="{{ client_list_url('Matured') }}" class="sales-metric-card-link">
                <div class="card sales-metric-card-green mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="card-header-text mb-1">Team Matured Sales</p>
                                <h3 class="card-value-text mb-0">{{ $adminData['total_sales'] ?? 0 }}</h3>
                            </div>
                            <div class="metric-badge-icon gradient-green-teal">
                                <i class="bx bx-check-double"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="text-success font-size-13"><i class="bx bx-up-arrow-alt align-middle"></i> Matured in Year {{ $adminData['selected_year'] }}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="#line-column-chart" class="sales-metric-card-link">
                <div class="card sales-metric-card-purple mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="card-header-text mb-1">Today's Team Callbacks</p>
                                <h3 class="card-value-text mb-0">{{ $adminData['todays_tbros_count'] ?? 0 }}</h3>
                            </div>
                            <div class="metric-badge-icon gradient-purple-blue">
                                <i class="bx bx-calendar-star"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="text-primary font-size-13"><i class="bx bx-time align-middle"></i> Scheduled for Today</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="#allocation-panel" class="sales-metric-card-link">
                <div class="card sales-metric-card-red mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="card-header-text mb-1">Unassigned Leads</p>
                                <h3 class="card-value-text mb-0">{{ count($adminData['unassigned_fresh_leads'] ?? []) }}</h3>
                            </div>
                            <div class="metric-badge-icon gradient-orange-red">
                                <i class="bx bx-user-plus"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="text-danger font-size-13"><i class="bx bx-info-circle align-middle"></i> Awaiting Allocation</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="#sales-team-oversight" class="sales-metric-card-link">
                <div class="card sales-metric-card-blue mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="card-header-text mb-1">Active Team Members</p>
                                <h3 class="card-value-text mb-0">{{ count($adminData['allocatable_team_members'] ?? []) }}</h3>
                            </div>
                            <div class="metric-badge-icon gradient-sky-blue">
                                <i class="bx bx-group"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="text-info font-size-13"><i class="bx bx-check-circle align-middle"></i> Online Executives</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Main Oversight Row -->
    <div class="row mb-4">
        <!-- Sales Team Oversight Heatmap -->
        <div class="col-xl-8">
            <div class="card sales-card-glass" id="sales-team-oversight">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="card-title text-premium-dark font-size-16 mb-1">⚡ Sales Team Oversight Heatmap</h4>
                            <p class="text-muted font-size-12 mb-0">Real-time breakdown of sales pipelines, active touchpoints, and follow-up delays.</p>
                        </div>
                    </div>

                    <div class="table-responsive custom-scroll" style="max-height: 400px;">
                        <table class="table table-centered table-nowrap mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Executive</th>
                                    <th class="text-center">Active Leads</th>
                                    <th class="text-center">Matured Deals</th>
                                    <th class="text-center">Callbacks Today</th>
                                    <th class="text-center">Overdue Follow-ups</th>
                                    <th class="text-center">Status Index</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adminData['team_performance_matrix'] ?? [] as $exec)
                                @if($exec->id !== $user->id) {{-- Exclude TL themselves from team heatmap --}}
                                <tr class="oversight-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs mr-3">
                                                <span class="avatar-title rounded-circle bg-soft-primary text-primary font-size-14">
                                                    {{ strtoupper(substr($exec->name, 0, 2)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h5 class="font-size-14 text-premium-dark mb-0">{{ $exec->name }}</h5>
                                                <p class="text-muted font-size-12 mb-0">{{ $exec->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-premium badge-premium-info">{{ $exec->active_leads_count }} Active</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-premium badge-premium-success font-weight-bold">{{ $exec->matured_leads_count }} Converted</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-premium badge-premium-warning">{{ $exec->today_callbacks_count }} Today</span>
                                    </td>
                                    <td class="text-center">
                                        @if($exec->overdue_callbacks_count > 0)
                                        <span class="badge-premium badge-premium-danger animated pulse infinite">{{ $exec->overdue_callbacks_count }} Overdue</span>
                                        @else
                                        <span class="badge-premium badge-premium-success">None</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($exec->overdue_callbacks_count > 3)
                                        <span class="text-danger font-size-18" title="High Delay Risk"><i class="bx bxs-error-circle animate-pulse"></i></span>
                                        @elseif($exec->overdue_callbacks_count > 0)
                                        <span class="text-warning font-size-18" title="Medium Delay Risk"><i class="bx bxs-alarm-warning"></i></span>
                                        @else
                                        <span class="text-success font-size-18" title="Fully on Track"><i class="bx bxs-check-shield"></i></span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($exec->overdue_callbacks_count > 0)
                                        <button class="btn btn-sm btn-soft-danger waves-effect waves-light nudge-exec-btn"
                                            data-exec-name="{{ $exec->name }}"
                                            data-exec-id="{{ $exec->id }}"
                                            data-overdue-count="{{ $exec->overdue_callbacks_count }}"
                                            title="Nudge for follow-up">
                                            Nudge
                                        </button>
                                        @else
                                        <span class="text-muted font-size-12">On Track</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No Sales Executives registered in your team.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Analytics Chart -->
        <div class="col-xl-4">
            <div class="card sales-card-glass">
                <div class="card-body">
                    <h4 class="card-title text-premium-dark font-size-16 mb-4">📈 Team Sales Performance</h4>
                    <div id="line-column-chart" class="apex-charts" dir="ltr" style="min-height: 350px;"></div>
                </div>
            </div>
            <!-- Team Status Stage Conversion Analytics Chart -->
            <div class="card sales-card-glass mt-4">
                <div class="card-body">
                    <h4 class="card-title text-premium-dark font-size-16 mb-4 d-flex align-items-center">
                        <i class="bx bx-pie-chart-alt-2 mr-2 text-primary"></i> Team Stage Distribution
                    </h4>
                    <div id="team-stage-pie-chart" class="apex-charts" dir="ltr" style="min-height: 250px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Allocation Panel & Unassigned Leads -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card sales-card-glass" id="allocation-panel">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="card-title text-premium-dark font-size-16 mb-1">📥 Fresh Lead Allocation Panel</h4>
                            <p class="text-muted font-size-12 mb-0">Instantly distribute newly imported leads and incoming prospects to active executives.</p>
                        </div>
                        <span class="badge badge-soft-danger font-size-12">{{ count($adminData['unassigned_fresh_leads'] ?? []) }} Leads Awaiting Allocation</span>
                    </div>

                    <div class="table-responsive custom-scroll" style="max-height: 350px;">
                        <table class="table table-centered table-nowrap align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Client Name</th>
                                    <th>Mobile</th>
                                    <th>Contact Person</th>
                                    <th>Referral Source</th>
                                    <th>Received Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adminData['unassigned_fresh_leads'] ?? [] as $lead)
                                <tr>
                                    <td class="font-weight-bold text-premium-dark">{{ $lead->name }}</td>
                                    <td>{{ $lead->mobile }}</td>
                                    <td>{{ $lead->cont_person ?? 'N/A' }}</td>
                                    <td>
                                        @if($lead->telereferral)
                                        <span class="badge badge-soft-primary">{{ $lead->telereferral->name }}</span>
                                        @else
                                        <span class="text-muted">Direct / Inward</span>
                                        @endif
                                    </td>
                                    <td>{{ $lead->created_at ? $lead->created_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary waves-effect waves-light open-allocation-modal-btn"
                                            data-client-id="{{ $lead->id }}"
                                            data-client-name="{{ $lead->name }}">
                                            <i class="bx bx-user-check align-middle mr-1"></i> Allocate Lead
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted font-size-13">🎉 Hurrah! All incoming leads have been allocated. No fresh leads currently pending.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         ALLOCATION TRIGGER MODAL (GLASSMORPHIC OVERLAY)
         ========================================================================= --}}
    <div class="modal fade" id="leadAllocationModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0" style="border-radius: 16px; overflow: hidden; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);">
                <div class="modal-header gradient-sky-blue text-white py-3">
                    <h5 class="modal-title font-size-16 text-white" id="allocModalTitle">Allocate Fresh Lead</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="leadAllocationForm">
                    @csrf
                    <input type="hidden" name="clientid" id="allocClientId">
                    <div class="modal-body p-4">
                        <p class="text-muted font-size-13">Choose an executive to assign to the client: <strong class="text-premium-dark" id="allocClientName"></strong></p>

                        <div class="form-group mb-0">
                            <label class="control-label text-premium-dark font-size-13 mb-2">Select Sales Executive</label>
                            <select class="form-control" name="executive" id="allocExecSelect" required style="border-radius: 8px;">
                                <option value="" disabled selected>-- Select Executive --</option>
                                @foreach($adminData['allocatable_team_members'] ?? [] as $exec)
                                @if($exec->id !== $user->id)
                                <option value="{{ $exec->id }}">{{ $exec->name }}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-3">
                        <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                        <button type="submit" class="btn btn-primary waves-effect waves-light" style="border-radius: 8px;">
                            <i class="bx bx-check-shield mr-1"></i> Confirm Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @else
    {{-- =========================================================================
         📞 SALES EXECUTIVE (EMPLOYEE) VIEW WITH AI CO-PILOT
         ========================================================================= --}}

    <!-- Pulse Metrics Row (Trendy, Modern Card Designs) -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <a href="{{ client_list_url('Matured') }}" class="sales-metric-card-link">
                <div class="card sales-metric-card-green mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="card-header-text mb-1">My Closed Sales</p>
                                <h3 class="card-value-text mb-0 font-weight-bold">{{ $adminData['matured_leads'] ?? 0 }}</h3>
                            </div>
                            <div class="metric-badge-icon gradient-green-teal">
                                <i class="mdi mdi-trophy"></i>
                            </div>
                        </div>
                        <div class="mt-3 d-flex align-items-center justify-content-between">
                            <span class="text-premium-muted font-size-11">Matured in {{ $adminData['selected_year'] }}</span>
                            <span class="badge badge-soft-success font-size-11">Active Closer</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="#datatable-section" class="sales-metric-card-link">
                <div class="card sales-metric-card-purple mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="card-header-text mb-1">Callbacks Today</p>
                                <h3 class="card-value-text mb-0 font-weight-bold">{{ $adminData['todays_callbacks_count'] ?? 0 }}</h3>
                            </div>
                            <div class="metric-badge-icon gradient-purple-blue">
                                <i class="mdi mdi-phone-in-talk"></i>
                            </div>
                        </div>
                        <div class="mt-3 d-flex align-items-center justify-content-between">
                            <span class="text-premium-muted font-size-11">Due in current shift</span>
                            @if(($adminData['todays_callbacks_count'] ?? 0) > 0)
                            <span class="badge badge-soft-primary animate-pulse font-size-11">Touchpoints Pending</span>
                            @else
                            <span class="badge badge-soft-success font-size-11">Cleared</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="#datatable-section" class="sales-metric-card-link">
                <div class="card sales-metric-card-red mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="card-header-text mb-1">Overdue Follow-ups</p>
                                <h3 class="card-value-text mb-0 font-weight-bold text-danger">{{ $adminData['overdue_callbacks_count'] ?? 0 }}</h3>
                            </div>
                            <div class="metric-badge-icon gradient-orange-red pulse-glow-red">
                                <i class="mdi mdi-clock"></i>
                            </div>
                        </div>
                        <div class="mt-3 d-flex align-items-center justify-content-between">
                            <span class="text-premium-muted font-size-11">Awaiting communication</span>
                            @if(($adminData['overdue_callbacks_count'] ?? 0) > 0)
                            <span class="badge badge-danger font-size-11 pulse-glow-red">Urgent Action</span>
                            @else
                            <span class="badge badge-soft-success font-size-11">On Track</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="#sales-stage-pie-chart" class="sales-metric-card-link">
                <div class="card sales-metric-card-blue mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="card-header-text mb-1">Conversion Ratio</p>
                                <h3 class="card-value-text mb-0 font-weight-bold">{{ $adminData['conversion_rate'] ?? 0 }}%</h3>
                            </div>
                            <div class="metric-badge-icon gradient-sky-blue">
                                <i class="mdi mdi-chart-pie"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress progress-sm" style="height: 6px; border-radius: 10px; background: rgba(0, 223, 254, 0.08);">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $adminData['conversion_rate'] ?? 0 }}%; border-radius: 10px;" aria-valuenow="{{ $adminData['conversion_rate'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- ⏰ Live Action Priority Callback Cards (Top Level Tray) -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card sales-card-glass" style="border: 1px solid rgba(127, 0, 255, 0.22) !important; background: linear-gradient(135deg, #fefbfe 0%, #ffffff 100%); border-radius: 16px; box-shadow: 0 10px 30px rgba(127, 0, 255, 0.03) !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2" style="border-bottom: 1px dashed rgba(127, 0, 255, 0.12);">
                        <div>
                            <h5 class="font-size-15 mb-1 d-flex align-items-center" style="color: #7F00FF; font-weight: 700;">
                                <span class="spinner-grow spinner-grow-sm mr-2 text-danger" role="status" style="width: 10px; height: 10px; animation-duration: 1.5s;"></span>
                                🔥 Today's Live Priority Callback Queue (Top-Level Live Actions)
                            </h5>
                            <p class="text-muted font-size-12 mb-0">High-priority touchpoints due during your current shift. Pitch or update statuses with single-click ease.</p>
                        </div>
                        <span class="badge badge-soft-danger font-size-11 px-3 py-1" style="border-radius: 20px;">
                            {{ count($adminData['todays_callbacks'] ?? []) }} Scheduled
                        </span>
                    </div>

                    <div class="d-flex flex-row flex-wrap py-2 custom-scroll" style="gap: 16px; max-height: 280px; overflow-y: auto; width: 100%;">
                        @forelse($adminData['todays_callbacks'] ?? [] as $callback)
                        <div class="card mb-0 flex-shrink-0 sales-callback-live-card" style="width: 310px; border: 1px solid rgba(127, 0, 255, 0.12); border-radius: 12px; background: #ffffff; box-shadow: 0 4px 12px rgba(127, 0, 255, 0.02); transition: all 0.2s ease; display: inline-block;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <div>
                                        <a href="{{ route('client.detail', [base64_encode($callback->id), 'sts']) }}" class="text-decoration-none">
                                            <h6 class="font-size-14 text-premium-dark mb-1 font-weight-bold text-truncate hover-primary-color" style="max-width: 190px; transition: color 0.2s ease;">{{ $callback->name }}</h6>
                                        </a>
                                        <p class="text-muted font-size-11 mb-1"><i class="mdi mdi-phone-outline"></i> {{ $callback->mobile }}</p>
                                        <p class="text-premium-muted font-size-10 mb-0" style="font-weight: 600; letter-spacing: 0.3px;"><i class="mdi mdi-calendar-clock"></i> LAST CONTACTED: {{ isset($callback->history->created_at) ? \Carbon\Carbon::parse($callback->history->created_at)->format('d M Y') : 'N/A' }}</p>
                                    </div>
                                    @if($callback->status === 'Followup')
                                    <span class="badge badge-soft-warning font-size-10 px-2 py-0.5" style="border-radius: 10px;">Followup</span>
                                    @elseif($callback->status === 'Meeting Fixed')
                                    <span class="badge badge-soft-info font-size-10 px-2 py-0.5" style="border-radius: 10px;">Meeting Fixed</span>
                                    @else
                                    <span class="badge badge-soft-primary font-size-10 px-2 py-0.5" style="border-radius: 10px;">{{ $callback->status }}</span>
                                    @endif
                                </div>

                                <a href="{{ route('client.detail', [base64_encode($callback->id), 'sts']) }}" class="text-decoration-none text-muted">
                                    <div class="p-2 mb-2" style="background: rgba(127, 0, 255, 0.015); border-radius: 8px; border: 1px dashed rgba(127, 0, 255, 0.08); cursor: pointer; transition: border-color 0.2s ease;">
                                        <p class="font-size-11 text-premium-muted mb-0" style="line-height: 1.45; word-break: break-word; white-space: normal;">
                                            <strong class="text-premium-dark">Remark:</strong> {{ $callback->history->remarks ?? 'No log remarks recorded' }}
                                        </p>
                                    </div>
                                </a>

                                <div class="d-flex justify-content-between mt-2" style="gap: 12px !important; width: 100%;">
                                    <button type="button" class="btn btn-xs btn-outline-primary w-50 text-center d-flex align-items-center justify-content-center update-sts-btn"
                                        data-client-id="{{ $callback->id }}"
                                        data-client-name="{{ $callback->name }}"
                                        style="border-radius: 8px; font-size: 11px; padding: 6px 0;">
                                        <i class="mdi mdi-clock-outline mr-1"></i> Update STS
                                    </button>
                                    <button type="button" class="btn btn-xs btn-primary w-50 text-center d-flex align-items-center justify-content-center update-dsr-btn"
                                        data-client-id="{{ $callback->id }}"
                                        data-client-name="{{ $callback->name }}"
                                        style="border-radius: 8px; font-size: 11px; padding: 6px 0;">
                                        <i class="mdi mdi-file-document-outline mr-1"></i> Update DSR
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="w-100 text-center py-4 bg-soft-success" style="border-radius: 12px; border: 1px dashed rgba(40, 199, 111, 0.25);">
                            <i class="bx bx-check-double text-success font-size-32 mb-1 animate-pulse"></i>
                            <h6 class="text-success font-weight-bold mb-1">Awesome! All callbacks are cleared.</h6>
                            <p class="text-muted font-size-11 mb-0">No further callbacks scheduled for today. Keep hunting for new leads!</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Pipeline list and Closings Row -->
    <div class="row mb-4">
        <!-- My Active Pipeline Overhaul (Interactive AI Co-Pilot Assistance Card Rows) -->
        <div class="col-xl-8">
            <div class="card sales-card-glass ai-highlight-border">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="card-title text-premium-dark font-size-16 mb-1 d-flex align-items-center">
                                <i class="bx bxs-magic-wand mr-2 font-size-18 text-primary pulse-glow-ai"></i>
                                Pipelines & Smart Co-Pilot
                            </h4>
                            <p class="text-muted font-size-12 mb-0">Dynamic conversion score analyses, customer sentiment logs, and personalized single-click drafts.</p>
                        </div>
                        <span class="badge badge-soft-primary font-size-12 px-3 py-1" style="border-radius: 30px;">
                            {{ count($adminData['my_active_leads'] ?? []) }} Leads in Workspace
                        </span>
                    </div>

                    <div class="custom-scroll pr-1" style="max-height: 480px; overflow-y: auto;">
                        @forelse($adminData['my_active_leads'] ?? [] as $lead)
                        <div class="ai-pipeline-item p-3 mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <div class="d-flex align-items-start">
                                        <div class="avatar-xs mr-3 mt-1">
                                            <span class="avatar-title rounded-circle bg-soft-primary text-primary font-weight-bold">
                                                {{ strtoupper(substr($lead->name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h5 class="font-size-14 text-premium-dark mb-1 font-weight-bold">{{ $lead->name }}</h5>
                                            <p class="text-muted font-size-12 mb-1"><i class="bx bx-phone"></i> {{ $lead->mobile }}</p>
                                            <div class="d-flex flex-wrap gap-2 mt-1">
                                                @if($lead->status === 'Followup')
                                                <span class="badge-premium badge-premium-warning">Followup</span>
                                                @elseif($lead->status === 'Meeting Fixed')
                                                <span class="badge-premium badge-premium-info">Meeting Fixed</span>
                                                @else
                                                <span class="badge-premium badge-premium-success">{{ $lead->status }}</span>
                                                @endif

                                                @if($lead->ai_sentiment === 'Highly Positive')
                                                <span class="badge badge-soft-success font-size-11"><i class="bx bxs-smile align-middle mr-1"></i> Highly Positive</span>
                                                @elseif($lead->ai_sentiment === 'Hesitant (Price)')
                                                <span class="badge badge-soft-warning font-size-11"><i class="bx bxs-meh align-middle mr-1"></i> Price Hesitant</span>
                                                @else
                                                <span class="badge badge-soft-secondary font-size-11"><i class="bx bxs-circle align-middle mr-1"></i> General / Neutral</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 text-md-center my-3 my-md-0" style="border-left: 1px solid rgba(0,0,0,0.04); border-right: 1px solid rgba(0,0,0,0.04);">
                                    <p class="text-premium-muted font-size-10 mb-1">CONVERSION SCORE</p>
                                    @if($lead->ai_score >= 80)
                                    <span class="ai-gauge-badge bg-soft-success text-success pulse-glow-ai">
                                        <i class="bx bxs-bolt mr-1 animate-pulse"></i> {{ $lead->ai_score }}% (HOT)
                                    </span>
                                    @elseif($lead->ai_score >= 50)
                                    <span class="ai-gauge-badge bg-soft-warning text-warning">
                                        <i class="bx bxs-star-half mr-1"></i> {{ $lead->ai_score }}% (WARM)
                                    </span>
                                    @else
                                    <span class="ai-gauge-badge bg-soft-secondary text-muted">
                                        <i class="bx bxs-snowflake mr-1"></i> {{ $lead->ai_score }}% (COOL)
                                    </span>
                                    @endif

                                    <div class="mt-2">
                                        @if($lead->history)
                                        @if($lead->history->tbro === \Carbon\Carbon::today()->toDateString())
                                        <span class="text-warning font-size-11 font-weight-bold"><i class="bx bx-alarm"></i> Today at {{ $lead->history->time ?? 'N/A' }}</span>
                                        @elseif($lead->history->tbro < \Carbon\Carbon::today()->toDateString())
                                            <span class="text-danger font-size-11 font-weight-bold blink-text"><i class="bx bx-error-circle"></i> OVERDUE callback</span>
                                            @else
                                            <span class="text-muted font-size-11"><i class="bx bx-calendar"></i> {{ \Carbon\Carbon::parse($lead->history->tbro)->format('M d') }}</span>
                                            @endif
                                            @else
                                            <span class="text-muted font-size-11">No scheduled callback</span>
                                            @endif
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-2 mb-2" style="background: rgba(127, 0, 255, 0.015); border-radius: 8px; border: 1px dashed rgba(127, 0, 255, 0.08); min-height: 54px;">
                                        <p class="font-size-11 text-premium-muted mb-0" style="line-height: 1.4;">
                                            <strong class="text-premium-dark"><i class="bx bx-message-detail text-primary mr-1"></i> Remarks:</strong>
                                            {{ $lead->history->remarks ?? 'No history remarks logged' }}
                                        </p>
                                    </div>
                                    <div>
                                        <a href="{{ route('client.detail', [base64_encode($lead->id), 'sts']) }}" class="btn btn-xs btn-primary waves-effect waves-light w-100 text-center d-flex align-items-center justify-content-center" style="border-radius: 8px; font-size: 11px; padding: 7px 0;">
                                            <i class="bx bx-right-top-arrow-circle mr-1"></i> Update Lead Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bx bx-circle font-size-48 text-premium-muted mb-3"></i>
                            <p class="mb-1 font-size-14 font-weight-bold">No active prospects assigned in selected year.</p>
                            <p class="mb-0 font-size-12">Search or import fresh clients to start your pipeline touchpoints.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Side Column with Stats Chart and Closings -->
        <div class="col-xl-4">
            <!-- Status Stage Conversion Analytics Chart -->
            <div class="card sales-card-glass mb-4">
                <div class="card-body">
                    <h4 class="card-title text-premium-dark font-size-16 mb-4 d-flex align-items-center">
                        <i class="bx bx-pie-chart-alt-2 mr-2 text-primary"></i> Stage Conversion Distribution
                    </h4>
                    <div id="sales-stage-pie-chart" class="apex-charts" dir="ltr" style="min-height: 250px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         🪄 AI SMART PITCH CO-PILOT MODAL (GLASSMORPHIC OVERLAY)
         ========================================================================= --}}
    <div class="modal fade" id="aiPitchModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0" style="border-radius: 20px; overflow: hidden; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(25px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <div class="modal-header py-3" style="background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%);">
                    <h5 class="modal-title font-size-16 text-white d-flex align-items-center font-weight-bold">
                        <i class="bx bxs-magic-wand mr-2 font-size-18 text-warning animate-bounce"></i>
                        AI Sales Co-Pilot Follow-Up Pitch
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 d-flex justify-content-between align-items-center" style="border-radius: 12px; background: rgba(127, 0, 255, 0.05); border: 1px dashed rgba(127, 0, 255, 0.2);">
                        <div>
                            <p class="mb-1 font-size-10 text-premium-muted" style="letter-spacing: 0.5px;">TARGET LEAD</p>
                            <h5 class="text-premium-dark mb-0 font-weight-bold" id="aiClientName">Company Name</h5>
                        </div>
                        <span class="badge badge-soft-primary" id="aiClientStatusBadge">Followup</span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">SELECT COMMUNICATION MEDIUM</label>
                        <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                            <label class="btn btn-outline-primary active w-100 font-size-12 py-2" id="pitchOptionWhatsApp" style="border-top-left-radius: 10px; border-bottom-left-radius: 10px;">
                                <input type="radio" name="pitchType" value="whatsapp" checked> <i class="bx bxl-whatsapp align-middle"></i> WhatsApp Pitch
                            </label>
                            <label class="btn btn-outline-primary w-100 font-size-12 py-2" id="pitchOptionEmail" style="border-top-right-radius: 10px; border-bottom-right-radius: 10px;">
                                <input type="radio" name="pitchType" value="email"> <i class="bx bx-envelope align-middle"></i> Professional Email
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">GENERATED DRAFT</label>
                        <textarea class="form-control font-size-13 text-premium-dark p-3" id="aiGeneratedDraftText" rows="8" style="border-radius: 12px; background: #fafafc; line-height: 1.6; border: 1px solid rgba(0,0,0,0.06); font-family: 'Inter', sans-serif;" readonly></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 d-flex justify-content-between">
                    <div>
                        <span class="text-success font-size-12 font-weight-bold" id="aiCopySuccessMsg" style="display: none;">
                            <i class="bx bx-check-circle mr-1"></i> Pitch copied successfully!
                        </span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                        <button type="button" class="btn btn-primary waves-effect waves-light" id="aiCopyPitchBtn" style="border-radius: 10px; background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%); border: none;">
                            <i class="bx bx-copy mr-1"></i> Copy to Clipboard
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endif

    <!-- Universal Daily Callback Queue (Highlighted Premium Alert Focus) -->
    <div class="row" id="datatable-section">
        <div class="col-lg-12">
            <div class="card sales-card-glass" style="border: 1px solid rgba(127, 0, 255, 0.4) !important; box-shadow: 0 15px 45px rgba(127, 0, 255, 0.08) !important; background: linear-gradient(to bottom, #ffffff, #fafbff);">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3" style="border-bottom: 1px dashed rgba(127, 0, 255, 0.15);">
                        <div>
                            <h4 class="card-title font-size-18 mb-1 d-flex align-items-center" style="color: #7F00FF; font-weight: 700;">
                                <i class="bx bxs-alarm-warning mr-2 animate-pulse" style="color: #FF416C; font-size: 22px;"></i>
                                ⏰ Today's Direct Callback & Follow-up Queue
                            </h4>
                            <p class="text-muted font-size-12 mb-0">Action mandatory: List of all customers scheduled for a critical follow-up touchpoint callback today.</p>
                        </div>
                        <span class="badge badge-danger font-size-12 px-3 py-1 animate-pulse" style="border-radius: 30px; box-shadow: 0 4px 12px rgba(255, 65, 108, 0.25);">
                            REQUIRED FOR TODAY
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable" class="table table-centered table-nowrap align-middle mb-0" style="width:100%;">
                            <thead class="table-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>Company</th>
                                    <th>Mobile</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Latest Log Remark</th>
                                    <th>Callback Date</th>
                                    @if($user->hasRole(['Team-Leader']) && empty($forceExecutiveView))
                                    <th>Assigned Executive</th>
                                    @endif
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Loaded Dynamically via Yajra DataTables inside sales_scripts --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 📋 DSR UPDATE MODAL (EXQUISITE DASHBOARD POPUP) -->
    <div class="modal fade" id="updateDsrModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0" style="border-radius: 20px; overflow: hidden; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(25px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <form id="dsrUpdateForm" method="POST" action="{{ route('client.createDsr') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="client_id" id="dsrClientId">

                    <div class="modal-header py-3" style="background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%);">
                        <h5 class="modal-title font-size-16 text-white d-flex align-items-center font-weight-bold">
                            <i class="mdi mdi-file-document-outline mr-2 font-size-18 text-warning animate-pulse"></i>
                            Update Daily Sales Report (DSR)
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body p-4" style="max-height: 480px; overflow-y: auto;">
                        <div class="p-3 mb-3 d-flex justify-content-between align-items-center" style="border-radius: 12px; background: rgba(127, 0, 255, 0.05); border: 1px dashed rgba(127, 0, 255, 0.2);">
                            <div>
                                <p class="mb-1 font-size-10 text-premium-muted" style="letter-spacing: 0.5px;">CLIENT PROFILE</p>
                                <h5 class="text-premium-dark mb-0 font-weight-bold" id="dsrClientNameLabel">Company Name</h5>
                            </div>
                            <span class="badge badge-soft-primary">DSR Update</span>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">DSR STATUS / LEAD CATEGORY <span class="text-danger">*</span></label>
                                @php
                                $dsrStatusList = App\Models\ParentStatus::where('category', 'DSR')
                                ->whereIn('name', ['Not Interested', 'Matured', 'Hot Prespective', 'Warm Prespective'])->get();
                                @endphp
                                <select class="form-control" name="dsr_status" id="dsrStatus" style="border-radius: 10px; border: 1px solid rgba(0,0,0,0.06); height: 42px;" required>
                                    <option value="" selected disabled>Select DSR Status</option>
                                    @foreach($dsrStatusList as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">TBRO TYPE <span class="text-danger">*</span></label>
                                <select class="form-control" name="tbro_type" id="dsrTbroType" style="border-radius: 10px; border: 1px solid rgba(0,0,0,0.06); height: 42px;" required>
                                    <option value="Direct Visit" selected>Direct Visit</option>
                                    <option value="Call">Call</option>
                                    <option value="WhatsApp">WhatsApp</option>
                                    <option value="Meeting">Meeting</option>
                                    <option value="Email">Email</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">FOLLOW-UP TIME <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="tbro_time" id="dsrTbroTime" style="border-radius: 10px; border: 1px solid rgba(0,0,0,0.06); height: 42px;" placeholder="e.g., 05:30 PM" required>
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">FOLLOW-UP DATE (TBRO) <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tbro_date" id="dsrTbroDate" style="border-radius: 10px; border: 1px solid rgba(0,0,0,0.06); height: 42px;" required>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">DAILY SALES REMARKS <span class="text-danger">*</span></label>
                            <textarea class="form-control font-size-13 text-premium-dark p-3" name="dsr_remarks" id="dsrRemarksInput" rows="4" style="border-radius: 12px; line-height: 1.5; border: 1px solid rgba(0,0,0,0.06); font-family: 'Inter', sans-serif;" placeholder="Enter specific touchpoint details, requirements, next steps..." required></textarea>
                        </div>

                        <div id="dsrMaturedWarning" class="alert alert-soft-warning mt-3 mb-0 font-size-11" style="display: none; border-radius: 10px;">
                            <i class="mdi mdi-alert mr-1"></i> Note: For maturing a client and recording advance payments/packages/contracts, please proceed to the client's detailed profile page.
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary waves-effect mr-2" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                        <button type="submit" class="btn btn-primary waves-effect waves-light" style="border-radius: 10px; background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%); border: none;">
                            <i class="bx bx-check-circle mr-1"></i> Save DSR Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 📋 STS UPDATE MODAL (EXQUISITE DASHBOARD POPUP) -->
    <div class="modal fade" id="updateStsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0" style="border-radius: 20px; overflow: hidden; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(25px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <form id="stsUpdateForm" method="POST" action="{{ route('client.createSts') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="client_id" id="stsClientId">

                    <div class="modal-header py-3" style="background: linear-gradient(135deg, #00C6FF 0%, #0072FF 100%);">
                        <h5 class="modal-title font-size-16 text-white d-flex align-items-center font-weight-bold">
                            <i class="mdi mdi-clock-outline mr-2 font-size-18 text-warning animate-pulse"></i>
                            Update Sales Touchpoint Status (STS)
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body p-4" style="max-height: 480px; overflow-y: auto;">
                        <div class="p-3 mb-3 d-flex justify-content-between align-items-center" style="border-radius: 12px; background: rgba(0, 114, 255, 0.05); border: 1px dashed rgba(0, 114, 255, 0.2);">
                            <div>
                                <p class="mb-1 font-size-10 text-premium-muted" style="letter-spacing: 0.5px;">CLIENT PROFILE</p>
                                <h5 class="text-premium-dark mb-0 font-weight-bold" id="stsClientNameLabel">Company Name</h5>
                            </div>
                            <span class="badge badge-soft-info">STS Update</span>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">STS STATUS / STAGE <span class="text-danger">*</span></label>
                                @php
                                $stsStatusList = App\Models\ParentStatus::where('category', 'STS')->get();
                                @endphp
                                <select class="form-control" name="sts_status" id="stsStatus" style="border-radius: 10px; border: 1px solid rgba(0,0,0,0.06); height: 42px;" required>
                                    <option value="" selected disabled>Select STS Status</option>
                                    @foreach($stsStatusList as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">TBRO TYPE <span class="text-danger">*</span></label>
                                <select class="form-control" name="tbro_type" id="stsTbroType" style="border-radius: 10px; border: 1px solid rgba(0,0,0,0.06); height: 42px;" required>
                                    <option value="Call" selected>Call</option>
                                    <option value="Direct visit">Direct visit</option>
                                    <option value="WhatsApp">WhatsApp</option>
                                    <option value="Meeting">Meeting</option>
                                    <option value="Email">Email</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">FOLLOW-UP TIME <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="tbro_time" id="stsTbroTime" style="border-radius: 10px; border: 1px solid rgba(0,0,0,0.06); height: 42px;" placeholder="e.g., 05:30 PM" required>
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">FOLLOW-UP DATE (TBRO) <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tbro_date" id="stsTbroDate" style="border-radius: 10px; border: 1px solid rgba(0,0,0,0.06); height: 42px;" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">ATTACHMENT TYPE</label>
                                <select class="form-control" name="attachment_type" id="stsAttachmentType" style="border-radius: 10px; border: 1px solid rgba(0,0,0,0.06); height: 42px;">
                                    <option value="" selected>Select attachment type</option>
                                    <option value="Company Profile">Company Profile</option>
                                    <option value="Brochure">Brochure</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">ATTACH FILE</label>
                                <div class="custom-file" style="height: 42px;">
                                    <input type="file" class="custom-file-input" name="attachment" id="stsAttachment" accept="image/*, .pdf" style="height: 42px;">
                                    <label class="custom-file-label d-flex align-items-center" for="stsAttachment" style="border-radius: 10px; border: 1px solid rgba(0,0,0,0.06); height: 42px;">Choose file</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="control-label text-premium-muted font-size-10 mb-2" style="letter-spacing: 0.5px;">TOUCHPOINT REMARKS (STS) <span class="text-danger">*</span></label>
                            <textarea class="form-control font-size-13 text-premium-dark p-3" name="sts_remarks" id="stsRemarksInput" rows="4" style="border-radius: 12px; line-height: 1.5; border: 1px solid rgba(0,0,0,0.06); font-family: 'Inter', sans-serif;" placeholder="Enter specific touchpoint status details, client feedback..." required></textarea>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary waves-effect mr-2" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                        <button type="submit" class="btn btn-info waves-effect waves-light" style="border-radius: 10px; background: linear-gradient(135deg, #00C6FF 0%, #0072FF 100%); border: none;">
                            <i class="bx bx-check-circle mr-1"></i> Save STS Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
