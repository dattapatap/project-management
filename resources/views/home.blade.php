@extends('layouts.app')

@section('content')

<div class="container-fluid erp-page pb-5">
    @if(isset($hasSubmittedClosingToday) && !$hasSubmittedClosingToday && \Carbon\Carbon::now()->hour >= 18 && Auth::user()->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant', 'Sales-Executive', 'CSD-Executive', 'Team-Leader']))
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger shadow-sm d-flex align-items-center justify-content-between mb-0" style="border-radius: 12px; background: linear-gradient(135deg, #f857a6 0%, #ff5858 100%); color: white; border: none;">
                <div class="d-flex align-items-center">
                    <div class="display-4 mr-3" style="font-size: 24px; line-height: 1;">⏰</div>
                    <div>
                        <h5 class="text-white font-weight-bold mb-1">EOD Day Closing Pending</h5>
                        <p class="text-white-50 mb-0 font-weight-medium">It is past 06:00 PM and you have not submitted your day-closing report yet. Please submit your summary now to complete your daily checklist.</p>
                    </div>
                </div>
                <a href="{{ route('day-closing.index') }}" class="btn btn-light btn-sm font-weight-bold px-4 rounded-pill shadow-sm" style="color: #ff5858; white-space: nowrap;">
                    <i class="mdi mdi-calendar-check-outline mr-1"></i> Submit Now
                </a>
            </div>
        </div>
    </div>
    @endif
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between float-right">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Home</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <!-- end page title -->

    {{-- Role-specific Dashboards with Tab Support --}}
    @php
    $activeTab = session('active_dashboard_tab');

    // Set defaults if session is empty
    if (!$activeTab) {
    if($user->hasRole('Team-Leader')) $activeTab = 'tab-tl-team';
    if($user->hasRole('Project-Manager')) $activeTab = 'tab-pm-portfolio';
    }
    @endphp

    @if($user->hasRole('Team-Leader'))
    {{-- Dashboard Tab Switcher for TL --}}
    <ul class="nav nav-tabs dashboard-tabs" id="tlDashboardTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab == 'tab-tl-team' ? 'active' : '' }}" href="{{ route('home', ['tab' => 'tab-tl-team']) }}">
                <i class="mdi mdi-shield-crown"></i> Team Oversight
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab == 'tab-tl-personal' ? 'active' : '' }}" href="{{ route('home', ['tab' => 'tab-tl-personal']) }}">
                <i class="mdi mdi-account-circle"></i> My Personal Workspace
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade {{ $activeTab == 'tab-tl-team' ? 'show active' : '' }} tab-content-animate" id="team-oversight" role="tabpanel">
            @if(optional($user->departments)->department == 1)
            @include('dashboards.tl_sales_ui')
            @elseif(optional($user->departments)->department == 3)
            @include('dashboards.tl_csd_ui')
            @else
            @include('dashboards.tl_od_ui')
            @endif
        </div>
        <div class="tab-pane fade {{ $activeTab == 'tab-tl-personal' ? 'show active' : '' }} tab-content-animate" id="my-workspace" role="tabpanel">
            @if(optional($user->departments)->department == 1)
            @include('dashboards.sales_ui', [
            'adminData' => !empty($personalData) ? $personalData : $adminData,
            'forceExecutiveView' => true
            ])
            @elseif(optional($user->departments)->department == 3)
            @include('dashboards.csd_ui', [
            'adminData' => !empty($personalData) ? $personalData : $adminData,
            'forceExecutiveView' => true
            ])
            @else
            @include('dashboards.employee_od_ui')
            @endif
        </div>
    </div>
    @elseif($user->isBranchManager())
    {{-- Branch Manager — all-department oversight --}}
    <div class="row">
        <div class="col-12">
            @include('dashboards.branch_manager_ui')
        </div>
    </div>
    @elseif($user->hasRole('Project-Manager'))
    {{-- Project Manager Portfolio View Only --}}
    <div class="row">
        <div class="col-12">
            @include('dashboards.pm_ui')
        </div>
    </div>
    @else
    {{-- Standard Dashboard Logic for others --}}
    @if($user->hasRole('Admin'))
    @include('dashboards.admin_ui')
    @endif

    @if($user->hasRole('Sales-Executive') && optional($user->departments)->department == 1)
    @include('dashboards.sales_ui')
    @endif

    @if($user->hasRole('CSD-Executive') && optional($user->departments)->department == 3)
    @include('dashboards.csd_ui')
    @endif

    @if($user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant']))
    @include('dashboards.employee_od_ui')
    @endif
    @endif


</div>

@endsection

@section('scripts')

{{-- Role-based Scripts --}}
@if($user->hasRole('Admin'))
@include('dashboards.admin_scripts')
@endif

@if($user->isBranchManager())
@include('dashboards.branch_manager_scripts')
@endif

@if($user->hasRole(['Sales-Executive', 'Team-Leader']) && optional($user->departments)->department == 1)
@include('dashboards.sales_scripts')
@endif

@if($user->hasRole('Team-Leader') && optional($user->departments)->department == 1)
@include('dashboards.tl_sales_scripts')
@endif

@if($user->hasRole(['Project-Manager']))
@include('dashboards.pm_scripts')
@endif

@if($user->hasRole('Team-Leader') && optional($user->departments)->department == 2)
@include('dashboards.tl_od_scripts')
@endif

@if($user->hasRole('Team-Leader') && optional($user->departments)->department == 3)
@include('dashboards.tl_csd_scripts')
@endif

@if($user->hasRole(['CSD-Executive', 'Team-Leader']) && optional($user->departments)->department == 3)
@include('dashboards.csd_scripts')
@endif

{{-- Employee Scripts --}}
@if($user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant', 'Team-Leader']))
@include('dashboards.employee_scripts')
@endif

@endsection
