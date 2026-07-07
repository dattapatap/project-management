@extends('layouts.app')

@section('styles')
<style>
    .targets-wrapper {
        font-family: 'Outfit', 'Inter', sans-serif;
    }

    .header-card-glass {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(245, 247, 255, 0.95) 100%);
        border-left: 5px solid #00c6ff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
    }

    .text-premium {
        color: #2b3a4a;
        font-weight: 700;
    }

    .custom-table th {
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.8px;
        color: #5c6a7a !important;
        font-weight: 700 !important;
        background-color: #f8fafc;
        border-bottom: 2px solid #edf2f7 !important;
    }

    .custom-table td {
        vertical-align: middle !important;
        color: #2d3748;
        font-size: 13.5px;
    }

    .target-input {
        width: 100px;
        height: 38px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        text-align: center;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .target-input:focus {
        border-color: #00c6ff;
        box-shadow: 0 0 0 3px rgba(0, 198, 255, 0.15);
        outline: none;
    }

    .btn-save-targets {
        background: linear-gradient(135deg, #0072ff 0%, #00c6ff 100%);
        border: none;
        color: white;
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(0, 114, 255, 0.2);
    }

    .btn-save-targets:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(0, 114, 255, 0.3);
        color: white;
    }

    .badge-dept {
        font-size: 11px;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 6px;
    }

    .badge-nsd {
        background-color: rgba(246, 173, 85, 0.15);
        color: #dd6b20;
    }

    .badge-csd {
        background-color: rgba(79, 209, 197, 0.15);
        color: #319795;
    }

    .badge-od {
        background-color: rgba(159, 122, 234, 0.15);
        color: #805ad5;
    }
</style>
@endsection

@section('content')
<div class="container-fluid targets-wrapper pb-5">

    <!-- Header Card -->
    <div class="card mb-4 header-card-glass">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h3 class="text-premium font-size-18 mb-1">🎯 Configure Daily Targets</h3>
                <p class="text-muted font-size-12 mb-0">Configure daily work target parameters and thresholds for executives.</p>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-3">
                <a href="{{ route('daily-targets.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm mr-2">
                    <i class="bx bx-arrow-back mr-1"></i> Back to Targets List
                </a>
                <ol class="breadcrumb m-0 bg-transparent p-0 font-size-12">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-primary"><i class="bx bx-home-alt"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('daily-targets.index') }}" class="text-primary">Daily Targets</a></li>
                    <li class="breadcrumb-item active text-muted">Configure</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Main Board -->
    <div class="card border shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr>
                            <th style="padding-left: 24px;">Team Member</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Daily Target Parameter & Value</th>
                            <th class="text-right" style="padding-right: 24px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                        <tr>
                            <td style="padding-left: 24px;">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs rounded-circle bg-light d-flex align-items-center justify-content-center font-weight-bold text-uppercase border mr-3" style="width: 32px; height: 32px;">
                                        {{ substr($u->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold">{{ $u->name }}</h6>
                                        <small class="text-muted">{{ $u->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-dept text-uppercase badge-{{ $u->dept_type }}">
                                    {{ $u->dept_type }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted">{{ $u->getRoleNames()->first() ?? '-' }}</span>
                            </td>
                            <td>
                                <form id="target-form-{{ $u->id }}" class="d-flex align-items-center flex-wrap gap-3">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $u->id }}">

                                    @if($u->dept_type === 'nsd')
                                    <div class="d-flex align-items-center mr-3">
                                        <span class="text-muted mr-2 font-weight-semibold font-size-12">Daily Work Hours:</span>
                                        <input type="number" name="targets[global_hours]" class="target-input form-control" value="{{ $u->configured_targets['global_hours'] }}" min="7">
                                    </div>
                                    <div class="d-flex align-items-center mr-3">
                                        <span class="text-muted mr-2 font-weight-semibold font-size-12">STS Target:</span>
                                        <input type="number" name="targets[sts_updates]" class="target-input form-control" value="{{ $u->configured_targets['sts_updates'] }}" min="1">
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted mr-2 font-weight-semibold font-size-12">DSR Target:</span>
                                        <input type="number" name="targets[dsr_updates]" class="target-input form-control" value="{{ $u->configured_targets['dsr_updates'] }}" min="1">
                                    </div>
                                    @elseif($u->dept_type === 'csd')
                                    <div class="d-flex align-items-center mr-3">
                                        <span class="text-muted mr-2 font-weight-semibold font-size-12">Daily Work Hours:</span>
                                        <input type="number" name="targets[global_hours]" class="target-input form-control" value="{{ $u->configured_targets['global_hours'] }}" min="7">
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted mr-2 font-weight-semibold font-size-12">Communications Target:</span>
                                        <input type="number" name="targets[communications]" class="target-input form-control" value="{{ $u->configured_targets['communications'] }}" min="1">
                                    </div>
                                    @elseif($u->dept_type === 'od')
                                    <div class="d-flex align-items-center mr-3">
                                        <span class="text-muted mr-2 font-weight-semibold font-size-12">Daily Work Hours:</span>
                                        <input type="number" name="targets[global_hours]" class="target-input form-control" value="{{ $u->configured_targets['global_hours'] }}" min="7">
                                    </div>
                                    <div class="d-flex align-items-center mr-3">
                                        <span class="text-muted mr-2 font-weight-semibold font-size-12">Task Hours:</span>
                                        <input type="number" name="targets[hours_logged]" class="target-input form-control" value="{{ $u->configured_targets['hours_logged'] }}" min="1">
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted mr-2 font-weight-semibold font-size-12">Completed Tasks:</span>
                                        <input type="number" name="targets[tasks_completed]" class="target-input form-control" value="{{ $u->configured_targets['tasks_completed'] }}" min="1">
                                    </div>
                                    @endif
                                </form>
                            </td>
                            <td class="text-right" style="padding-right: 24px;">
                                <button type="button" class="btn btn-save-targets btn-sm shadow-sm" onclick="saveUserTargets({{ $u->id }})">
                                    <i class="mdi mdi-check mr-1"></i>Save
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function saveUserTargets(userId) {
        var formData = $('#target-form-' + userId).serialize();
        $.ajax({
            url: "{{ route('daily-targets.store') }}",
            method: "POST",
            data: formData,
            success: function(response) {
                if (response.success) {
                    alertify.success(response.message);
                } else {
                    alertify.error(response.message);
                }
            },
            error: function(xhr) {
                alertify.error(xhr.responseJSON?.message || "Failed to update daily targets.");
            }
        });
    }
</script>
@endsection
