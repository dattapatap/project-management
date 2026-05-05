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

    {{-- Admin Dashboard --}}
    @if($user->hasRole('Admin'))
    @include('dashboards.admin_ui')
    @endif


    {{-- Sales Executive / Team Leader Dept 1 --}}
    @if($user->hasRole(['Sales-Executive', 'Team-Leader']) && $user->departments->department == 1)
    @include('dashboards.sales_ui')
    @endif


    {{-- Project Manager Dashboard --}}
    @if($user->hasRole(['Project-Manager']))
    @include('dashboards.pm_ui')
    @endif


    {{-- Team Leader Dashboard (OD Department) --}}
    @if($user->hasRole(['Team-Leader']) && $user->departments->department == 2)
    @include('dashboards.tl_od_ui')
    @endif

    {{-- Employee / Team Metrics (OD Department) --}}
    @if($user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant', 'Team-Leader']))
    @include('dashboards.employee_od_ui')
    @endif


</div>

@endsection

@section('scripts')

{{-- Role-based Scripts --}}
@if($user->hasRole('Admin'))
@include('dashboards.admin_scripts')
@endif

@if($user->hasRole(['Sales-Executive', 'Team-Leader']) && $user->departments->department == 1)
@include('dashboards.sales_scripts')
@endif

@if($user->hasRole(['Project-Manager']))
@include('dashboards.pm_scripts')
@endif

@if($user->hasRole(['Team-Leader']) && $user->departments->department == 2)
@include('dashboards.tl_od_scripts')
@endif

{{-- Employee Scripts --}}
@if($user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant', 'Team-Leader']))
@include('dashboards.employee_scripts')
@endif

@endsection
