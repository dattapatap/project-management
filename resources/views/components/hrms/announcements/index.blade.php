@extends('layouts.app')

@section('content')
<div class="container-fluid pb-5">
    {{-- Page Header --}}
    <div class="card mb-4 border shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #ffffff 0%, #fdfbfb 100%);">
        <div class="card-body py-3.5 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="mr-3 d-flex align-items-center justify-content-center text-primary" style="background: #eef2ff; border-radius: 14px; width: 48px; height: 48px; font-size: 24px;">
                    <i class="mdi mdi-bullhorn-outline"></i>
                </div>
                <div>
                    <h4 class="mb-0 text-dark font-weight-bold font-size-18">Company Announcements & Broadcasts</h4>
                    <span class="text-muted font-size-12">Publish and broadcast notices, urgent alerts, and event updates across the organization</span>
                </div>
            </div>
            <div class="d-flex align-items-center mt-2 mt-md-0" style="gap: 8px;">
                <a href="{{ route('hrms.celebrations.index') }}" class="btn btn-sm btn-outline-secondary px-3 font-weight-bold" style="border-radius: 8px;">
                    <i class="mdi mdi-cake-variant mr-1"></i> Celebrations
                </a>
                <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#addAnnouncementModal" style="border-radius: 8px; height: 36px;">
                    <i class="mdi mdi-plus mr-1"></i> New Announcement
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
        <i class="mdi mdi-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
        <i class="mdi mdi-alert-circle mr-1"></i> <strong>Please resolve the following errors:</strong>
        <ul class="mb-0 mt-1 pl-3 font-size-12">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    {{-- Stats Row --}}
    @php
        $totalCount = $announcements->count();
        $activeCount = $announcements->where('is_active', true)->filter(function($a) {
            $today = \Carbon\Carbon::today()->format('Y-m-d');
            return $a->start_date->format('Y-m-d') <= $today && $a->end_date->format('Y-m-d') >= $today;
        })->count();
        $urgentCount = $announcements->where('type', 'urgent')->where('is_active', true)->count();
        $totalReads = $announcements->sum('reads_count');
    @endphp
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border shadow-sm h-100" style="border-radius: 14px; border-left: 4px solid #4f46e5 !important;">
                <div class="card-body py-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Total Broadcasts</span>
                        <h3 class="mb-0 font-weight-bold text-dark mt-1 font-size-22">{{ $totalCount }}</h3>
                    </div>
                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-primary" style="background: #eef2ff; width: 44px; height: 44px; font-size: 22px;">
                        <i class="mdi mdi-bullhorn"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border shadow-sm h-100" style="border-radius: 14px; border-left: 4px solid #10b981 !important;">
                <div class="card-body py-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Currently Active</span>
                        <h3 class="mb-0 font-weight-bold text-dark mt-1 font-size-22">{{ $activeCount }}</h3>
                    </div>
                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-success" style="background: #ecfdf5; width: 44px; height: 44px; font-size: 22px;">
                        <i class="mdi mdi-broadcast"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border shadow-sm h-100" style="border-radius: 14px; border-left: 4px solid #ef4444 !important;">
                <div class="card-body py-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Urgent Alerts</span>
                        <h3 class="mb-0 font-weight-bold text-dark mt-1 font-size-22">{{ $urgentCount }}</h3>
                    </div>
                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-danger" style="background: #fef2f2; width: 44px; height: 44px; font-size: 22px;">
                        <i class="mdi mdi-alert-decagram"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border shadow-sm h-100" style="border-radius: 14px; border-left: 4px solid #f59e0b !important;">
                <div class="card-body py-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Total Read Receipts</span>
                        <h3 class="mb-0 font-weight-bold text-dark mt-1 font-size-22">{{ $totalReads }}</h3>
                    </div>
                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-warning" style="background: #fffbeb; width: 44px; height: 44px; font-size: 22px;">
                        <i class="mdi mdi-check-all"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Announcements Table Card --}}
    <div class="card border shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="mb-0 font-size-15 font-weight-bold text-dark">
                <i class="mdi mdi-format-list-bulleted mr-1 text-primary"></i> Broadcast Announcements List
            </h5>
            <span class="badge badge-light border text-muted px-2.5 py-1 font-size-12">{{ $announcements->count() }} items</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4 text-muted font-size-11 text-uppercase font-weight-bold" style="width: 50px;">#</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold" style="min-width: 250px;">Title & Message</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold" style="width: 100px;">Type</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold" style="min-width: 150px;">Audience Scope</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold" style="min-width: 160px;">Date Schedule</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold" style="width: 100px;">Status</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold text-center" style="width: 90px;">Reads</th>
                            <th class="py-3 px-4 text-right text-muted font-size-11 text-uppercase font-weight-bold" style="width: 130px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($announcements as $idx => $announcement)
                        @php
                            $today = \Carbon\Carbon::today()->format('Y-m-d');
                            $sDate = $announcement->start_date->format('Y-m-d');
                            $eDate = $announcement->end_date->format('Y-m-d');

                            $isExpired = $eDate < $today;
                            $isUpcoming = $sDate > $today;
                            $isCurrent = !$isExpired && !$isUpcoming;

                            $typeColor = match($announcement->type) {
                                'urgent' => 'danger',
                                'event'  => 'purple',
                                default  => 'info',
                            };
                            $typeIcon = match($announcement->type) {
                                'urgent' => 'mdi-alert-circle',
                                'event'  => 'mdi-calendar-star',
                                default  => 'mdi-information-outline',
                            };
                        @endphp
                        <tr>
                            <td class="px-4 font-weight-bold text-muted font-size-12">{{ $idx + 1 }}</td>
                            <td class="px-3">
                                <span class="font-weight-bold text-dark font-size-13 d-block">{{ $announcement->title }}</span>
                                <small class="text-muted font-size-11 d-block text-truncate" style="max-width: 320px;">
                                    {{ Str::limit($announcement->message, 80) }}
                                </small>
                                <small class="text-muted font-size-10 mt-0.5 d-block">
                                    By: {{ $announcement->creator?->name ?? 'Admin' }} &bull; {{ $announcement->created_at->diffForHumans() }}
                                </small>
                            </td>
                            <td class="px-3">
                                <span class="badge badge-soft-{{ $typeColor }} px-2 py-1 rounded font-size-11 font-weight-bold text-uppercase">
                                    <i class="mdi {{ $typeIcon }} mr-1"></i>{{ $announcement->type }}
                                </span>
                            </td>
                            <td class="px-3">
                                @if(!$announcement->target_dept && !$announcement->target_branch)
                                    <span class="badge badge-soft-primary px-2 py-1 rounded font-size-11 font-weight-bold">
                                        <i class="mdi mdi-earth mr-1"></i> Company-Wide
                                    </span>
                                @else
                                    <div class="d-flex flex-column" style="gap: 2px;">
                                        @if($announcement->target_dept)
                                            <span class="badge badge-soft-dark px-2 py-0.5 rounded font-size-10 font-weight-semibold text-left">
                                                <i class="mdi mdi-office-building mr-1 text-primary"></i> Dept: {{ $announcement->department?->name ?? 'Dept #' . $announcement->target_dept }}
                                            </span>
                                        @endif
                                        @if($announcement->target_branch)
                                            <span class="badge badge-soft-secondary px-2 py-0.5 rounded font-size-10 font-weight-semibold text-left">
                                                <i class="mdi mdi-map-marker mr-1 text-info"></i> Branch: {{ $announcement->branch?->name ?? 'Branch #' . $announcement->target_branch }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-3">
                                <div class="font-size-11 font-weight-semibold text-dark">
                                    {{ $announcement->start_date->format('d M, Y') }} &rarr; {{ $announcement->end_date->format('d M, Y') }}
                                </div>
                                @if($isExpired)
                                    <span class="badge badge-light text-muted border px-1.5 py-0.5 font-size-10 mt-1 font-weight-medium">Expired</span>
                                @elseif($isUpcoming)
                                    <span class="badge badge-soft-warning px-1.5 py-0.5 font-size-10 mt-1 font-weight-bold">Starts Soon</span>
                                @else
                                    <span class="badge badge-soft-success px-1.5 py-0.5 font-size-10 mt-1 font-weight-bold">Live Now</span>
                                @endif
                            </td>
                            <td class="px-3">
                                @if($announcement->is_active)
                                    <span class="badge badge-soft-success px-2 py-1 rounded font-size-11 font-weight-bold">Active</span>
                                @else
                                    <span class="badge badge-soft-secondary px-2 py-1 rounded font-size-11 font-weight-bold">Inactive</span>
                                @endif
                            </td>
                            <td class="px-3 text-center">
                                <span class="badge badge-light border text-dark font-weight-bold px-2 py-1 font-size-11" title="Number of users who clicked 'Ok / Mark as Read'">
                                    <i class="mdi mdi-account-check mr-1 text-success"></i>{{ $announcement->reads_count }}
                                </span>
                            </td>
                            <td class="px-4 text-right">
                                {{-- Status Toggle Form --}}
                                <form action="{{ route('hrms.announcements.status', $announcement->id) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-light border btn-sm px-2 py-1 mr-1 text-{{ $announcement->is_active ? 'warning' : 'success' }}" title="{{ $announcement->is_active ? 'Deactivate' : 'Activate' }}" style="border-radius: 6px;">
                                        <i class="mdi {{ $announcement->is_active ? 'mdi-eye-off-outline' : 'mdi-eye-outline' }} font-size-13"></i>
                                    </button>
                                </form>

                                {{-- Edit Button --}}
                                <button type="button" class="btn btn-light border btn-sm px-2 py-1 mr-1 btn-edit-announcement"
                                    data-id="{{ $announcement->id }}"
                                    data-title="{{ $announcement->title }}"
                                    data-message="{{ $announcement->message }}"
                                    data-type="{{ $announcement->type }}"
                                    data-target_dept="{{ $announcement->target_dept }}"
                                    data-target_branch="{{ $announcement->target_branch }}"
                                    data-start_date="{{ $announcement->start_date->format('Y-m-d') }}"
                                    data-end_date="{{ $announcement->end_date->format('Y-m-d') }}"
                                    data-is_active="{{ $announcement->is_active ? '1' : '0' }}"
                                    title="Edit Announcement" style="border-radius: 6px;">
                                    <i class="mdi mdi-pencil text-muted font-size-13"></i>
                                </button>

                                {{-- Delete Form --}}
                                <form action="{{ route('hrms.announcements.destroy', $announcement->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to permanently delete this announcement?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light border btn-sm px-2 py-1 text-danger" title="Delete Announcement" style="border-radius: 6px;">
                                        <i class="mdi mdi-trash-can-outline font-size-13"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="mdi mdi-bullhorn-outline font-size-32 text-muted d-block mb-2 opacity-50"></i>
                                <span class="font-weight-medium">No announcements published yet.</span>
                                <small class="d-block text-muted mt-1">Click "New Announcement" above to broadcast notifications to team members.</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Announcement Modal --}}
<div class="modal fade" id="addAnnouncementModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 16px; border: 1px solid #e2e8f0;">
            <form action="{{ route('hrms.announcements.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light py-3 px-4 border-bottom">
                    <h5 class="modal-title font-size-15 font-weight-bold text-dark mb-0">
                        <i class="mdi mdi-bullhorn mr-1 text-primary"></i> Create New Announcement
                    </h5>
                    <button type="button" class="close btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-row mb-3">
                        <div class="form-group col-md-8 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Announcement Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control font-size-13" placeholder="e.g. Annual Company Meetup or Office Maintenance Alert" required style="border-radius: 8px;">
                        </div>
                        <div class="form-group col-md-4 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Priority / Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-control font-size-13" required style="border-radius: 8px;">
                                <option value="info">General Info ℹ️</option>
                                <option value="urgent">Urgent / Alert 🚨</option>
                                <option value="event">Event / Celebration 🎉</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Target Department (Optional)</label>
                            <select name="target_dept" class="form-control font-size-13" style="border-radius: 8px;">
                                <option value="">All Departments (Company-Wide)</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted font-size-10">Leave blank for all departments</small>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Target Branch (Optional)</label>
                            <select name="target_branch" class="form-control font-size-13" style="border-radius: 8px;">
                                <option value="">All Branches (Company-Wide)</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted font-size-10">Leave blank for all branches</small>
                        </div>
                    </div>

                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control font-size-13" value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" required style="border-radius: 8px;">
                            <small class="text-muted font-size-10">Date popup begins showing</small>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control font-size-13" value="{{ \Carbon\Carbon::today()->addDays(7)->format('Y-m-d') }}" required style="border-radius: 8px;">
                            <small class="text-muted font-size-10">Date popup ceases automatically</small>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-size-12 font-weight-bold text-dark">Announcement Content / Message <span class="text-danger">*</span></label>
                        <textarea name="message" rows="4" class="form-control font-size-13" placeholder="Provide full details, guidelines, venue, instructions, or actions required from team members..." required style="border-radius: 8px;"></textarea>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="addIsActive" checked>
                        <label class="custom-control-label font-size-12 text-dark font-weight-semibold" for="addIsActive">
                            Publish as Active immediately
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-3 font-weight-medium btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" style="border-radius: 8px;">Publish Broadcast</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Announcement Modal --}}
<div class="modal fade" id="editAnnouncementModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 16px; border: 1px solid #e2e8f0;">
            <form id="editAnnouncementForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light py-3 px-4 border-bottom">
                    <h5 class="modal-title font-size-15 font-weight-bold text-dark mb-0">
                        <i class="mdi mdi-pencil mr-1 text-primary"></i> Edit Announcement
                    </h5>
                    <button type="button" class="close btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-row mb-3">
                        <div class="form-group col-md-8 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Announcement Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="edit-title" class="form-control font-size-13" required style="border-radius: 8px;">
                        </div>
                        <div class="form-group col-md-4 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Priority / Type <span class="text-danger">*</span></label>
                            <select name="type" id="edit-type" class="form-control font-size-13" required style="border-radius: 8px;">
                                <option value="info">General Info ℹ️</option>
                                <option value="urgent">Urgent / Alert 🚨</option>
                                <option value="event">Event / Celebration 🎉</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Target Department (Optional)</label>
                            <select name="target_dept" id="edit-target_dept" class="form-control font-size-13" style="border-radius: 8px;">
                                <option value="">All Departments (Company-Wide)</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Target Branch (Optional)</label>
                            <select name="target_branch" id="edit-target_branch" class="form-control font-size-13" style="border-radius: 8px;">
                                <option value="">All Branches (Company-Wide)</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="edit-start_date" class="form-control font-size-13" required style="border-radius: 8px;">
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="edit-end_date" class="form-control font-size-13" required style="border-radius: 8px;">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-size-12 font-weight-bold text-dark">Announcement Content / Message <span class="text-danger">*</span></label>
                        <textarea name="message" id="edit-message" rows="4" class="form-control font-size-13" required style="border-radius: 8px;"></textarea>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="edit-is_active">
                        <label class="custom-control-label font-size-12 text-dark font-weight-semibold" for="edit-is_active">
                            Active Status
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-3 font-weight-medium btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" style="border-radius: 8px;">Update Broadcast</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.btn-edit-announcement').on('click', function() {
            var id = $(this).data('id');
            var title = $(this).data('title');
            var message = $(this).data('message');
            var type = $(this).data('type');
            var targetDept = $(this).data('target_dept') || '';
            var targetBranch = $(this).data('target_branch') || '';
            var startDate = $(this).data('start_date');
            var endDate = $(this).data('end_date');
            var isActive = $(this).data('is_active') == '1';

            $('#edit-title').val(title);
            $('#edit-message').val(message);
            $('#edit-type').val(type);
            $('#edit-target_dept').val(targetDept);
            $('#edit-target_branch').val(targetBranch);
            $('#edit-start_date').val(startDate);
            $('#edit-end_date').val(endDate);
            $('#edit-is_active').prop('checked', isActive);

            var actionUrl = "{{ url('hrms/announcements') }}/" + id;
            $('#editAnnouncementForm').attr('action', actionUrl);

            $('#editAnnouncementModal').modal('show');
        });
    });
</script>
@endsection
