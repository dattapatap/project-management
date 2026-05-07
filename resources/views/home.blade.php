@extends('layouts.app')
@section('styles')

<style>
    .pm-dashboard-custom-card {
        border: 1.5px solid #f0f0f0 !important;
        border-radius: 14px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03) !important;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .pm-dashboard-custom-card:hover {
        transform: translateY(-8px);
        border-color: #556ee6 !important;
        box-shadow: 0 20px 40px rgba(85, 110, 230, 0.08) !important;
    }

    .admin-kpi-card {
        border-radius: 12px !important;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        z-index: 1;
    }

    .admin-kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
    }

    .gradient-primary {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .gradient-success {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .gradient-warning {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .gradient-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .admin-kpi-icon {
        color: rgba(255, 255, 255, 0.8);
        transition: transform 0.3s ease;
    }

    .admin-kpi-card:hover .admin-kpi-icon {
        transform: scale(1.1);
        color: #ffffff;
    }

    .trendy-card {
        transition: all 0.3s ease;
    }

    .action-btn-trendy {
        border-radius: 10px;
        font-weight: 600;
        letter-spacing: 0.3px;
        transition: all 0.2s ease;
    }

    .action-btn-trendy:hover {
        transform: translateY(-2px);
    }

    .trendy-table th {
        border-top: none;
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .trendy-table tbody tr {
        transition: background-color 0.2s ease;
    }

    .trendy-table tbody tr:hover {
        background-color: #fcfcfc;
    }

    @keyframes pulse-red {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(244, 106, 106, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(244, 106, 106, 0);
        }

        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(244, 106, 106, 0);
        }
    }

    .pulse {
        animation: pulse-red 2s infinite;
    }

    /* Premium Dashboard Tabs */
    .dashboard-tabs {
        border-bottom: none !important;
        gap: 10px;
        margin-bottom: 25px;
    }

    .dashboard-tabs .nav-link {
        border: none !important;
        border-radius: 12px !important;
        padding: 12px 24px !important;
        font-weight: 700 !important;
        color: #74788d !important;
        background: #f8f9fa !important;
        transition: all 0.3s ease !important;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .dashboard-tabs .nav-link i {
        font-size: 18px;
    }

    .dashboard-tabs .nav-link.active {
        background: #556ee6 !important;
        color: #fff !important;
        box-shadow: 0 4px 15px rgba(85, 110, 230, 0.25) !important;
    }

    .dashboard-tabs .nav-link:hover:not(.active) {
        background: #edf0f7 !important;
        transform: translateY(-2px);
    }

    .tab-content-animate {
        animation: slideUpFade 0.4s ease-out;
    }

    @keyframes slideUpFade {
        from {
            opacity: 0;
            transform: translateY(15px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

@endsection
@section('content')


<div class="container-fluid pb-5">
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
            @else
            @include('dashboards.employee_od_ui')
            @endif
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

@if($user->hasRole(['Sales-Executive', 'Team-Leader']) && optional($user->departments)->department == 1)
@include('dashboards.sales_scripts')
@endif

@if($user->hasRole('Team-Leader') && optional($user->departments)->department == 1)
@include('dashboards.tl_sales_scripts')
@endif

@if($user->hasRole(['Project-Manager']))
@include('dashboards.pm_scripts')
@endif

@if($user->hasRole(['Team-Leader']) && optional($user->departments)->department == 2)
@include('dashboards.tl_od_scripts')
@endif

{{-- Employee Scripts --}}
@if($user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant', 'Team-Leader']))
@include('dashboards.employee_scripts')
@endif

@endsection
