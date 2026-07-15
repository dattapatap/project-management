@extends('layouts.app')

@section('styles')
<style>
    /* Styling System Custom Tokens */
    .dept-details-wrapper {
        font-family: 'Outfit', 'Inter', -apple-system, sans-serif;
        padding-bottom: 2rem;
    }

    .btn-back-premium {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #4b5563;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
    }

    .btn-back-premium:hover {
        background: #f8fafc;
        color: #4f46e5;
        border-color: #cbd5e1;
        transform: translateX(-4px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.08);
    }

    /* Tabs Styling */
    .nav-tabs-premium {
        border-bottom: 2px solid #f1f5f9 !important;
        gap: 8px;
        margin-bottom: 24px;
        background: transparent;
    }

    .nav-tabs-premium .nav-item {
        margin-bottom: -2px;
    }

    .nav-tabs-premium .nav-link {
        border: none !important;
        font-size: 14px;
        font-weight: 600;
        color: #64748b !important;
        padding: 12px 24px !important;
        border-radius: 0 !important;
        border-bottom: 2px solid transparent !important;
        transition: all 0.25s ease-in-out;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .nav-tabs-premium .nav-link.active {
        color: #4f46e5 !important;
        background: transparent !important;
        border-bottom-color: #4f46e5 !important;
    }

    .nav-tabs-premium .nav-link:hover:not(.active) {
        color: #0f172a !important;
        border-bottom-color: #cbd5e1 !important;
    }

    /* Card Details Container */
    .details-card-premium {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        padding: 24px;
        margin-bottom: 24px;
    }

    /* Premium Table styling */
    .table-premium {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0 6px;
    }

    .table-premium thead th {
        border: none;
        color: #475569;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        background: #f1f5f9;
        border-radius: 0px;
    }

    .table-premium tbody tr {
        transition: all 0.2s ease;
        background: #ffffff;
    }

    .table-premium tbody tr:hover {
        background: #f8fafc !important;
        transform: scale(1.001) translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
    }

    .table-premium tbody td {
        padding: 14px 16px;
        border-top: 1px solid #f1f5f9;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 14px;
        vertical-align: middle;
    }

    .table-premium tbody tr td:first-child {
        border-left: 1px solid #f1f5f9;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
        font-weight: 600;
        color: #64748b;
    }

    .table-premium tbody tr td:last-child {
        border-right: 1px solid #f1f5f9;
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    .avatar-circle-premium {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 2px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        object-fit: cover;
    }

    /* High-contrast Role badges */
    .badge-premium {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 6px;
        letter-spacing: 0.1px;
        margin-left: 6px;
        display: inline-block;
    }

    .badge-leader {
        background-color: rgba(245, 158, 11, 0.08);
        color: #b45309;
        border: 1px solid rgba(245, 158, 11, 0.15);
    }

    .badge-manager {
        background-color: rgba(16, 185, 129, 0.08);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.15);
    }

    /* Code outline styling */
    .emp-code-premium {
        font-size: 13px;
        font-weight: 700;
        color: #4f46e5;
        background-color: rgba(79, 70, 229, 0.05);
        padding: 4px 8px;
        border-radius: 6px;
        letter-spacing: 0.2px;
        text-transform: uppercase;
        border: 1px solid rgba(79, 70, 229, 0.1);
    }

    /* Beautiful Empty State */
    .empty-state-card {
        padding: 60px 20px;
        text-align: center;
        width: 100%;
    }

    .empty-state-icon {
        font-size: 64px;
        color: #94a3b8;
        animation: floatAnimation 3s ease-in-out infinite;
        display: inline-block;
        margin-bottom: 20px;
    }

    @keyframes floatAnimation {
        0% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }

        100% {
            transform: translateY(0px);
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid dept-details-wrapper">
    <!-- Header Navigation bar -->
    <div class="row mb-3">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <a href="{{ url('/departments') }}" class="btn-back-premium mr-3" data-toggle="tooltip" title="Back to Departments">
                    <i class="mdi mdi-keyboard-backspace"></i>
                </a>
                <div>
                    <h4 class="mb-0 font-size-18 font-weight-bold text-dark">{{ $departments->name }} Department</h4>
                    <span class="text-muted font-size-13">Configure members, roles, and branch resources.</span>
                </div>
            </div>

            <div class="page-title-right d-none d-sm-block">
                <ol class="breadcrumb m-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-muted">{{ env('APP_NAME') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/departments') }}" class="text-muted">Departments</a></li>
                    <li class="breadcrumb-item active font-weight-semibold text-primary">{{ $departments->name }}</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Main Workspace Card -->
    <div class="row">
        <div class="col-12">
            <div class="details-card-premium">
                <!-- Navigation Tab Strip -->
                <ul class="nav nav-tabs nav-tabs-premium" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0)" role="tab">
                            <i class="mdi mdi-account-multiple-outline"></i>
                            <span>Department Members</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('departments/'. $departments->name .'/'.'teams' ) }}" role="tab">
                            <i class="mdi mdi-account-group-outline"></i>
                            <span>Associated Teams</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab Content Panel -->
                <div class="tab-content">
                    <div class="tab-pane active" id="members" role="tabpanel">
                        @if(!$departments->users->isEmpty())
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 table-hover table-premium">
                                <thead class="thead-custom-teal">
                                    <tr>
                                        <th scope="col" style="width: 8%">Sl</th>
                                        <th scope="col" style="width: 8%;" class="text-center">Avatar</th>
                                        <th scope="col" style="width: 25%;">Full Name</th>
                                        <th scope="col" style="width: 15%;">Employee Code</th>
                                        <th scope="col" style="width: 15%;">System Role</th>
                                        <th scope="col" style="width: 15%;">Designation</th>
                                        <th scope="col" style="width: 14%;">Aligned Since</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($departments->users as $items)
                                    @php
                                    $users = \App\Models\User::with('emp')->where('id', $items->user)->first();
                                    @endphp
                                    @if($users)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="text-center">
                                            @if ($users->profile)
                                            <img class="avatar-circle-premium" title="{{ $users->name }}" src="{{ asset('storage/'. $users->profile )}}">
                                            @else
                                            <img class="avatar-circle-premium" title="{{ $users->name }}" src="{{ Avatar::create($users->name)->toBase64() }}">
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-weight-semibold text-dark">{{ $users->name }}</span>
                                            @if($users->hasRole("Team-Leader"))
                                            <span class="badge-premium badge-leader">Team-Leader</span>
                                            @endif
                                            @if($users->hasRole("Project-Manager"))
                                            <span class="badge-premium badge-manager">Project-Manager</span>
                                            @endif
                                        </td>
                                        <td>
                                            <code class="emp-code-premium">{{ $users->emp->mem_code ?? 'N/A' }}</code>
                                        </td>
                                        <td>
                                            <span class="text-muted font-weight-medium">{{ $users->roles->pluck('name')[0] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-dark font-weight-medium">{{ $users->emp->designation ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="font-weight-semibold text-muted">{{ Carbon\Carbon::parse($items->created_at)->format('d M Y') }}</span>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <!-- Custom Empty State -->
                        <div class="empty-state-card">
                            <div class="empty-state-icon">
                                <i class="mdi mdi-account-multiple-outline"></i>
                            </div>
                            <h3 class="font-weight-bold text-dark mb-2">No Members Assigned</h3>
                            <p class="text-muted mb-4 fs-14" style="max-width: 420px; margin: 0 auto;">There are currently no staff accounts aligned to this department unit. Allocate team members to this group via User settings.</p>
                            <a href="{{ url('/users') }}" class="btn btn-primary btn-sm rounded-lg px-4 font-weight-semibold" style="background-color: #4f46e5; border-color: #4f46e5;">
                                <i class="mdi mdi-account-plus mr-1"></i>Assign Team Members
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endsection
