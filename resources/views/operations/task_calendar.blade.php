@extends('layouts.app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
    .legend-color-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }
    
    /* FullCalendar Custom Premium Styling */
    .fc {
        color: #1e293b !important;
        font-family: 'Outfit', 'Inter', sans-serif;
    }
    #calendar-container, .fc-view-harness, .fc-daygrid-day {
        background-color: #ffffff !important;
    }
    .fc-theme-standard td, .fc-theme-standard th {
        border: 1px solid #e2e8f0 !important;
        background-color: #ffffff !important;
    }
    .fc-theme-standard .fc-scrollgrid {
        border: 1px solid #e2e8f0 !important;
        background-color: #ffffff !important;
    }
    .fc-daygrid-day-number {
        color: #475569 !important;
        font-weight: 600;
        text-decoration: none !important;
    }
    .fc-col-header-cell-cmn {
        background-color: #f8fafc !important;
        color: #1e293b !important;
        padding: 10px 0 !important;
        font-weight: 700;
    }
    .fc-button-primary {
        background-color: #7F00FF !important;
        border-color: #7F00FF !important;
        font-weight: 600 !important;
        text-transform: capitalize !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 10px rgba(127,0,255,0.15) !important;
        transition: all 0.2s ease !important;
    }
    .fc-button-primary:hover, .fc-button-primary:focus, .fc-button-primary:active {
        background-color: #6a00d8 !important;
        border-color: #6a00d8 !important;
        box-shadow: 0 6px 15px rgba(127,0,255,0.25) !important;
    }
    .fc-event {
        cursor: pointer;
        padding: 3px 6px !important;
        border-radius: 6px !important;
        border: none !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: transform 0.15s ease;
    }
    .fc-event:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .card-detail-glow {
        border-left: 4px solid #7F00FF;
        transition: all 0.3s ease;
    }
</style>
@endsection

@section('content')
<div class="container-fluid erp-page pb-5">
    <div class="erp-page-header my-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="erp-page-header__main">
            <h4 class="erp-page-title mb-1 text-premium-dark font-size-18">
                <i class="mdi mdi-calendar-month-outline mr-2 text-primary"></i>Operations Task Calendar
            </h4>
            <p class="text-muted font-size-13 mb-0 font-weight-medium">Manage and check schedules, timelines, and milestones.</p>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            @if($subordinates->isNotEmpty())
            <div class="d-flex align-items-center mr-3">
                <label class="mr-2 mb-0 font-weight-semibold text-muted font-size-12">Filter By Employee:</label>
                <select id="employee-filter" class="form-control form-control-sm border select2" style="width: 200px;">
                    <option value="">All Team Members</option>
                    @foreach($subordinates as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm rounded-pill font-weight-bold px-3">
                <i class="mdi mdi-arrow-left mr-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Legend & Details -->
        <div class="col-lg-3">
            <!-- Legend Card -->
            <div class="card bg-white shadow-sm border mb-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h5 class="text-dark font-weight-bold mb-3 font-size-14"><i class="mdi mdi-format-list-bulleted-type mr-1 text-primary"></i> Task Statuses</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center mb-2.5 text-dark font-weight-semibold font-size-12">
                            <span class="legend-color-dot mr-2.5" style="background: #10b981;"></span> Completed Task
                        </li>
                        <li class="d-flex align-items-center mb-2.5 text-dark font-weight-semibold font-size-12">
                            <span class="legend-color-dot mr-2.5" style="background: #f59e0b;"></span> In Progress Task
                        </li>
                        <li class="d-flex align-items-center mb-2.5 text-dark font-weight-semibold font-size-12">
                            <span class="legend-color-dot mr-2.5" style="background: #3b82f6;"></span> To Do (Standard)
                        </li>
                        <li class="d-flex align-items-center mb-0 text-dark font-weight-semibold font-size-12">
                            <span class="legend-color-dot mr-2.5" style="background: #ef4444;"></span> High / Urgent Priority
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Task Hover Details Box -->
            <div class="card bg-white shadow-sm border card-detail-glow mb-4" id="task-detail-card" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h5 class="text-dark font-weight-bold mb-3 font-size-14"><i class="mdi mdi-information-outline mr-1 text-primary"></i> Task Details</h5>
                    <hr class="my-2" style="border-color: #edf2f7;">
                    
                    <div id="no-task-placeholder" class="text-center py-4 text-muted">
                        <i class="mdi mdi-gesture-tap font-size-36 d-block mb-2 text-muted-50"></i>
                        <p class="font-size-11 mb-0 font-weight-medium">Hover over a calendar task to review details instantly.</p>
                    </div>

                    <div id="task-details-content" class="text-dark font-size-13 d-none">
                        <p class="mb-3">
                            <span class="text-muted font-size-10 font-weight-bold d-block text-uppercase letter-spacing-1">Task Title</span> 
                            <strong id="detail-title" class="text-premium-dark font-size-14">-</strong>
                        </p>
                        <p class="mb-2.5">
                            <span class="text-muted font-size-10 font-weight-bold d-block text-uppercase letter-spacing-1">Project Link</span> 
                            <span id="detail-project" class="font-weight-semibold text-primary">-</span>
                        </p>
                        <p class="mb-2.5">
                            <span class="text-muted font-size-10 font-weight-bold d-block text-uppercase letter-spacing-1">Assigned To</span> 
                            <span id="detail-assignee" class="font-weight-semibold">-</span>
                        </p>
                        <p class="mb-2.5">
                            <span class="text-muted font-size-10 font-weight-bold d-block text-uppercase letter-spacing-1">Status / Priority</span> 
                            <span id="detail-status-priority" class="font-weight-bold">-</span>
                        </p>
                        <div class="row">
                            <div class="col-6 mb-2.5">
                                <span class="text-muted font-size-10 font-weight-bold d-block text-uppercase letter-spacing-1">Start Date</span> 
                                <span id="detail-start" class="font-weight-semibold">-</span>
                            </div>
                            <div class="col-6 mb-2.5">
                                <span class="text-muted font-size-10 font-weight-bold d-block text-uppercase letter-spacing-1">End Date</span> 
                                <span id="detail-end" class="font-weight-semibold text-danger">-</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="#" id="detail-action-link" class="btn btn-primary btn-md btn-block font-weight-bold shadow-sm" style="border-radius: 10px;">
                                <i class="mdi mdi-open-in-new mr-1"></i> Open Task Board
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- The Calendar -->
        <div class="col-lg-9">
            <div class="card bg-white shadow-sm border" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <div id="calendar-container"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
    $(document).ready(function() {
        var calendarEl = document.getElementById('calendar-container');
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            events: function(info, successCallback, failureCallback) {
                var employeeId = $('#employee-filter').val() || '';
                
                $.ajax({
                    url: "{{ route('operations.calendar.events') }}",
                    type: 'GET',
                    data: {
                        start: info.startStr.split('T')[0],
                        end: info.endStr.split('T')[0],
                        user_id: employeeId
                    },
                    success: function(res) {
                        successCallback(res);
                    },
                    error: function() {
                        failureCallback();
                    }
                });
            },
            eventMouseEnter: function(info) {
                var props = info.event.extendedProps;
                
                // Set detail card content
                $('#detail-title').text(info.event.title);
                $('#detail-project').text(props.project);
                $('#detail-assignee').text(props.assignee);
                
                var statusClass = props.status === 'Completed' ? 'badge badge-soft-success' : (props.status === 'InProgress' ? 'badge badge-soft-warning' : 'badge badge-soft-primary');
                var priorityClass = (props.priority === 'High' || props.priority === 'Urgent') ? 'badge badge-soft-danger' : 'badge badge-soft-secondary';
                
                $('#detail-status-priority').html(
                    `<span class="${statusClass} font-size-11 px-2.5 mr-1">${props.status}</span>` +
                    `<span class="${priorityClass} font-size-11 px-2.5">${props.priority}</span>`
                );
                
                $('#detail-start').text(props.start_date);
                $('#detail-end').text(props.end_date);
                
                var boardUrl = "{{ url('projects/taskboard') }}/" + props.project_id;
                $('#detail-action-link').attr('href', boardUrl);
                
                // Show content & Hide placeholder
                $('#no-task-placeholder').addClass('d-none');
                $('#task-details-content').removeClass('d-none');
                
                // Change detail card border left to event color
                $('#task-detail-card').css('border-left-color', info.event.backgroundColor);
            },
            eventClick: function(info) {
                var props = info.event.extendedProps;
                var boardUrl = "{{ url('projects/taskboard') }}/" + props.project_id;
                window.location.href = boardUrl;
            }
        });
        
        calendar.render();
        
        // 🔄 Live filter refresh when dropdown filter changes
        $('#employee-filter').on('change', function() {
            calendar.refetchEvents();
        });
    });
</script>
@endsection
