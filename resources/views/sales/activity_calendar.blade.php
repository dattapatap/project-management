@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

<div class="container-fluid erp-page pb-5">
    <div class="erp-page-header my-4">
        <div class="erp-page-header__main">
            <h4 class="erp-page-title">
                <i class="mdi mdi-calendar-clock mr-2 text-primary"></i>Sales Activity Calendar
            </h4>
            <p class="erp-page-subtitle">View and filter callbacks</p>
        </div>
        <div class="erp-page-header__actions d-flex align-items-center">
            <select id="calendar-month-selector" class="form-control form-control-sm border mr-2" style="width: 120px; height: 31px;">
                @for($m=1; $m<=12; $m++)
                    <option value="{{ sprintf('%02d', $m) }}" {{ $m == date('n') ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>
            <select id="calendar-year-selector" class="form-control form-control-sm border mr-2" style="width: 100px; height: 31px;">
                @for($y=date('Y')-3; $y<=date('Y')+3; $y++)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left mr-1"></i>Back
            </a>
        </div>
    </div>

    <!-- Calendar Layout -->
    <div class="row">
        <!-- Sidebar Legend -->
        <div class="col-lg-3">
            <div class="card bg-white shadow-sm border mb-4">
                <div class="card-body">
                    <h5 class="text-dark font-weight-bold mb-3"><i class="mdi mdi-format-list-bulleted-type mr-1 text-primary"></i> Legend</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center mb-2 text-dark font-weight-semibold font-size-13">
                            <span class="legend-color-dot mr-2" style="background: #3b82f6;"></span> Fresh Leads
                        </li>
                        <li class="d-flex align-items-center mb-2 text-dark font-weight-semibold font-size-13">
                            <span class="legend-color-dot mr-2" style="background: #06b6d4;"></span> Followup
                        </li>
                        <li class="d-flex align-items-center mb-2 text-dark font-weight-semibold font-size-13">
                            <span class="legend-color-dot mr-2" style="background: #a855f7;"></span> Meeting Fixed
                        </li>
                        <li class="d-flex align-items-center mb-2 text-dark font-weight-semibold font-size-13">
                            <span class="legend-color-dot mr-2" style="background: #ef4444;"></span> Hot Perspective
                        </li>
                        <li class="d-flex align-items-center mb-2 text-dark font-weight-semibold font-size-13">
                            <span class="legend-color-dot mr-2" style="background: #f59e0b;"></span> Warm Perspective
                        </li>
                        <li class="d-flex align-items-center mb-2 text-dark font-weight-semibold font-size-13">
                            <span class="legend-color-dot mr-2" style="background: #10b981;"></span> Matured
                        </li>
                        <li class="d-flex align-items-center mb-2 text-dark font-weight-semibold font-size-13">
                            <span class="legend-color-dot mr-2" style="background: #6b7280;"></span> Not Interested
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Context Tooltip Card -->
            <div class="card bg-white shadow-sm border d-none mb-4" id="event-detail-card">
                <div class="card-body">
                    <h5 class="text-dark font-weight-bold mb-3"><i class="mdi mdi-information-outline mr-1 text-primary"></i> Callback Details</h5>
                    <hr class="my-2" style="border-color: #e2e8f0;">
                    <div class="text-dark font-size-13">
                        <p class="mb-1"><span class="text-muted font-size-11 d-block">LEAD/CLIENT</span> <strong id="evt-client" class="text-primary">-</strong></p>
                        <p class="mb-1"><span class="text-muted font-size-11 d-block">CONTACT PERSON</span> <span id="evt-contact" class="font-weight-semibold">-</span></p>
                        <p class="mb-1"><span class="text-muted font-size-11 d-block">SCHEDULED TIME</span> <span id="evt-time" class="font-weight-semibold">-</span></p>
                        <p class="mb-1"><span class="text-muted font-size-11 d-block">EXECUTIVE</span> <span id="evt-executive" class="font-weight-semibold">-</span></p>
                        <p class="mb-0"><span class="text-muted font-size-11 d-block">REMARKS</span> <span id="evt-remarks" class="small text-muted font-weight-medium">-</span></p>
                    </div>
                    <div class="mt-3">
                        <a href="#" id="evt-link" class="btn btn-primary btn-sm btn-block shadow-sm">Open Client Details</a>
                    </div>
                </div>
            </div>

            <!-- Day & Month Activities Overview Card -->
            <div class="card bg-white shadow-sm border mb-4">
                <div class="card-body">
                    <h5 class="text-dark font-weight-bold mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="mdi mdi-clipboard-text-clock mr-1 text-primary"></i> <span id="activities-title" class="font-size-14">Activities List</span></span>
                        <button id="show-all-month-btn" class="btn btn-xs btn-outline-primary d-none" style="padding: 1px 6px; font-size: 10px;">Show Month</button>
                    </h5>
                    <hr class="my-2" style="border-color: #e2e8f0;">
                    <div id="activities-list-container" style="max-height: 280px; overflow-y: auto;">
                        <p class="text-muted text-center my-3 font-size-12">No activities loaded yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- The Calendar -->
        <div class="col-lg-9">
            <div class="card bg-white shadow-sm border">
                <div class="card-body p-3">
                    <div id="calendar-container"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .legend-color-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }
    
    /* FullCalendar Premium Standard Custom Theme Overrides */
    .fc {
        color: #1e293b !important;
        font-family: inherit;
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
        padding: 8px 0 !important;
        font-weight: 700;
    }
    .fc-button-primary {
        background-color: #3b82f6 !important;
        border-color: #3b82f6 !important;
        font-weight: 600 !important;
        text-transform: capitalize !important;
    }
    .fc-button-primary:hover {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
    }
    .fc-button-active {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }
    .fc-event {
        cursor: pointer;
        padding: 4px 6px !important;
        border-radius: 6px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        border: none !important;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .fc-event, .fc-event-title, .fc-event-time {
        color: #ffffff !important;
    }
    .fc-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .hover-bg-light:hover {
        background-color: #f1f5f9 !important;
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
    let calendar;

    function highlightCalendarEvent(eventId) {
        if (!calendar) return;
        const evt = calendar.getEventById(eventId);
        if (evt) {
            // Trigger calendar detail tooltip click
            const clickInfo = {
                event: evt,
                jsEvent: { preventDefault: function() {} }
            };
            calendar.trigger('eventClick', clickInfo);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar-container');
        const detailCard = document.getElementById('event-detail-card');
        const showAllMonthBtn = document.getElementById('show-all-month-btn');
        const monthSelector = document.getElementById('calendar-month-selector');
        const yearSelector = document.getElementById('calendar-year-selector');

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            titleFormat: { year: 'numeric', month: 'long' }, // Expose year in title explicitly
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: "{{ route('sales.calendar.events') }}",
            eventClick: function(info) {
                info.jsEvent.preventDefault(); // Don't redirect directly
                
                const props = info.event.extendedProps;
                
                // Populate and show the Detail Sidebar Card
                document.getElementById('evt-client').textContent = info.event.title;
                document.getElementById('evt-contact').textContent = props.contact_person || 'N/A';
                
                let timeStr = 'N/A';
                if (info.event.start) {
                    timeStr = info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                }
                document.getElementById('evt-time').textContent = timeStr;
                document.getElementById('evt-executive').textContent = props.executive || 'N/A';
                document.getElementById('evt-remarks').textContent = props.remarks || 'No remarks provided.';
                
                const link = document.getElementById('evt-link');
                link.href = info.event.url;
                
                detailCard.classList.remove('d-none');
                detailCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            },
            dateClick: function(info) {
                // Filter sidebar list on date click
                updateActivitiesList(info.dateStr);
            },
            datesSet: function(info) {
                // Sync year/month dropdowns if view changed
                const current = calendar.getDate();
                const m = String(current.getMonth() + 1).padStart(2, '0');
                const y = current.getFullYear();
                
                if (monthSelector) monthSelector.value = m;
                if (yearSelector && yearSelector.querySelector(`option[value="${y}"]`)) {
                    yearSelector.value = y;
                }

                // When view changes (month, week, day), refresh sidebar overview list
                setTimeout(function() {
                    updateActivitiesList();
                }, 500); // Wait for events to load from feed
            }
        });

        calendar.render();

        // Sync selectors to calendar jumps
        function syncCalendarDate() {
            const year = yearSelector.value;
            const month = monthSelector.value;
            calendar.gotoDate(`${year}-${month}-01`);
        }

        if (monthSelector) monthSelector.addEventListener('change', syncCalendarDate);
        if (yearSelector) yearSelector.addEventListener('change', syncCalendarDate);

        // Show all month button event handler
        showAllMonthBtn.addEventListener('click', function() {
            updateActivitiesList();
        });

        // Group & display activities in sidebar
        function updateActivitiesList(selectedDateStr = null) {
            const events = calendar.getEvents();
            const container = document.getElementById('activities-list-container');
            const titleEl = document.getElementById('activities-title');

            if (!events || events.length === 0) {
                container.innerHTML = '<p class="text-muted text-center my-3 font-size-12">No activities loaded.</p>';
                return;
            }

            let filteredEvents = events;
            if (selectedDateStr) {
                filteredEvents = events.filter(evt => {
                    if (!evt.start) return false;
                    const datePart = evt.start.toISOString().split('T')[0];
                    return datePart === selectedDateStr;
                });
                const dateObj = new Date(selectedDateStr);
                titleEl.textContent = "Callbacks: " + dateObj.toLocaleDateString([], { month: 'short', day: 'numeric' });
                showAllMonthBtn.classList.remove('d-none');
            } else {
                const currentView = calendar.view;
                const startStr = currentView.activeStart.toISOString().split('T')[0];
                const endStr = currentView.activeEnd.toISOString().split('T')[0];
                
                filteredEvents = events.filter(evt => {
                    if (!evt.start) return false;
                    const datePart = evt.start.toISOString().split('T')[0];
                    return datePart >= startStr && datePart < endStr;
                });
                
                const titleDate = calendar.getDate();
                titleEl.textContent = titleDate.toLocaleDateString([], { month: 'short', year: 'numeric' }) + " List";
                showAllMonthBtn.classList.add('d-none');
            }

            if (filteredEvents.length === 0) {
                container.innerHTML = '<p class="text-muted text-center my-3 font-size-12">No callbacks for this range.</p>';
                return;
            }

            // Group events by date part
            const grouped = {};
            filteredEvents.forEach(evt => {
                if (!evt.start) return;
                const dateKey = evt.start.toISOString().split('T')[0];
                if (!grouped[dateKey]) {
                    grouped[dateKey] = [];
                }
                grouped[dateKey].push(evt);
            });

            const sortedDates = Object.keys(grouped).sort();

            let html = '';
            sortedDates.forEach(dateStr => {
                const dayEvents = grouped[dateStr];
                const dateObj = new Date(dateStr + 'T00:00:00');
                const formattedDay = dateObj.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' });

                html += `<div class="mb-3">
                    <div class="font-size-11 font-weight-bold text-muted border-bottom pb-1 mb-1.5" style="text-transform: uppercase; letter-spacing: 0.5px;">
                        ${formattedDay}
                    </div>`;
                
                dayEvents.forEach(evt => {
                    const props = evt.extendedProps;
                    let timeStr = '10:00 AM';
                    if (evt.start) {
                        timeStr = evt.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    }
                    
                    html += `<div class="p-2 mb-1.5 rounded border bg-light hover-bg-light d-flex flex-column" style="cursor: pointer; font-size: 12px; transition: background-color 0.2s;" onclick="highlightCalendarEvent('${evt.id}')">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-dark text-truncate mr-2" style="max-width: 130px;">${evt.title.split(' - ')[0]}</strong>
                            <span class="badge text-white" style="background-color: ${evt.backgroundColor || '#3b82f6'}; font-size: 9px; padding: 2px 4px;">${timeStr}</span>
                        </div>
                        <div class="text-muted font-size-11 mt-1">
                            <i class="mdi mdi-account-star-outline mr-0.5"></i> ${props.executive || 'Unassigned'}
                        </div>
                    </div>`;
                });

                html += `</div>`;
            });

            container.innerHTML = html;
        }
    });
</script>
@endsection
