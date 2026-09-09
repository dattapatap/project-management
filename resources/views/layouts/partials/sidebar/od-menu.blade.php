@php
$isPmOrTl = $user->hasRole(['Project-Manager', 'Team-Leader']);
@endphp

{{-- OPERATIONS · OD --}}
<!-- <li class="menu-title menu-title-dept">
    <span class="dept-label">Operations</span>
</li> -->
<li class="dept-item dept-item--od {{ (request()->is('projects') || (request()->is('projects*') && !request()->is('projects/timeline') && !request()->is('projects/resources') && !request()->is('projects/tasks*'))) ? 'mm-active' : '' }}">
    <a href="{{ url('/projects') }}" class="waves-effect {{ (request()->is('projects') || (request()->is('projects*') && !request()->is('projects/timeline') && !request()->is('projects/resources') && !request()->is('projects/tasks*'))) ? 'active' : '' }}">
        <i class="mdi mdi-folder-multiple-outline"></i><span>{{ $isPmOrTl ? 'Projects' : 'My Tasks' }}</span>
    </a>
</li>

@if ($isPmOrTl)
<li class="dept-item dept-item--od {{ request()->is('projects/tasks*') ? 'mm-active' : '' }}">
    <a href="{{ route('tasks.index') }}" class="waves-effect {{ request()->is('projects/tasks*') ? 'active' : '' }}">
        <i class="mdi mdi-checkbox-multiple-marked-circle-outline"></i><span>Tasks List</span>
    </a>
</li>
<li class="dept-item dept-item--od {{ request()->is('projects/timeline*') ? 'mm-active' : '' }}">
    <a href="{{ route('projects.timeline') }}" class="waves-effect {{ request()->is('projects/timeline*') ? 'active' : '' }}">
        <i class="mdi mdi-chart-gantt"></i><span>Gantt Timeline</span>
    </a>
</li>
<li class="dept-item dept-item--od {{ request()->is('projects/resources*') ? 'mm-active' : '' }}">
    <a href="{{ route('projects.resources') }}" class="waves-effect {{ request()->is('projects/resources*') ? 'active' : '' }}">
        <i class="mdi mdi-account-switch"></i><span>Resource Allocation</span>
    </a>
</li>
@endif

<li class="dept-item dept-item--od {{ request()->is('operations/calendar*') ? 'mm-active' : '' }}">
    <a href="{{ route('operations.calendar.index') }}" class="waves-effect {{ request()->is('operations/calendar*') ? 'active' : '' }}">
        <i class="mdi mdi-calendar-clock"></i><span>Activity Calendar</span>
    </a>
</li>

{{-- DAY CLOSING --}}
<li class="dept-item dept-item--main {{ request()->is('day-closing') && !request()->is('day-closing/approvals*') ? 'mm-active' : '' }}">
    <a class="waves-effect {{ request()->is('day-closing') && !request()->is('day-closing/approvals*') ? 'active' : '' }}" href="{{ route('day-closing.index') }}">
        <i class="mdi mdi-calendar-check-outline"></i><span>Day Closing</span>
    </a>
</li>
@if ($isPmOrTl)
<li class="dept-item dept-item--main {{ request()->is('day-closing/approvals*') ? 'mm-active' : '' }}">
    <a class="waves-effect {{ request()->is('day-closing/approvals*') ? 'active' : '' }}" href="{{ route('day-closing.approvals') }}">
        <i class="mdi mdi-checkbox-marked-outline"></i><span>Closing Approvals</span>
    </a>
</li>
@endif

{{-- HRMS MODULE --}}
<li class="dept-item dept-item--main {{ request()->is('hrms*') ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('hrms*') ? 'active' : '' }}">
        <i class="mdi mdi-account-group-outline"></i><span>HRMS</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ request()->is('hrms*') ? 'true' : 'false' }}">
        <li class="{{ request()->is('hrms/my-leaves*') ? 'mm-active' : '' }}"><a href="{{ route('hrms.my-leaves.index') }}">My Leaves</a></li>
    </ul>
</li>

{{-- ANALYTICS & REPORTS --}}
<li class="dept-item dept-item--reports {{ request()->is('my-insights*') || request()->is('reports/projects*') || request()->is('reports/operations*') || request()->is('reports/employee*') ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('my-insights*') || request()->is('reports/projects*') || request()->is('reports/operations*') || request()->is('reports/employee*') ? 'active' : '' }}">
        <i class="mdi mdi-chart-bar"></i><span>Analytics & Reports</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ request()->is('my-insights*') || request()->is('reports/projects*') || request()->is('reports/operations*') || request()->is('reports/employee*') ? 'true' : 'false' }}">
        <li class="{{ request()->is('my-insights*') ? 'mm-active' : '' }}"><a href="{{ route('my-insights') }}">My Insights</a></li>
        @if ($isPmOrTl)
        <li class="{{ request()->is('reports/projects*') ? 'mm-active' : '' }}"><a href="{{ route('reports.projects') }}">Project Reports</a></li>
        <li class="{{ request()->is('reports/operations*') ? 'mm-active' : '' }}"><a href="{{ route('reports.operations') }}">Department Reports</a></li>
        <li class="{{ request()->is('reports/employee*') ? 'mm-active' : '' }}"><a href="{{ route('reports.employees') }}">Employee Performance</a></li>
        @endif
    </ul>
</li>
