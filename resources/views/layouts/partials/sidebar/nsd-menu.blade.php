{{-- SALES · NSD --}}
<li class="menu-title menu-title-dept">
    <span class="dept-label">Sales Operations</span>
</li>
<li class="dept-item dept-item--nsd {{ is_client_list_route() ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ is_client_list_route() ? 'active' : '' }}">
        <i class="mdi mdi-domain"></i><span>Companies</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ is_client_list_route() ? 'true' : 'false' }}">
        <li class="{{ client_category_active('fresh') ? 'mm-active' : '' }}"><a href="{{ client_list_url('Fresh') }}">Fresh</a></li>
        <li class="{{ client_category_active('matured') ? 'mm-active' : '' }}"><a href="{{ client_list_url('Matured') }}">Matured</a></li>
        <li class="{{ client_category_active('followup') ? 'mm-active' : '' }}"><a href="{{ client_list_url('followup') }}">Follow-up</a></li>
        <li class="{{ client_category_active('not-interested') ? 'mm-active' : '' }}"><a href="{{ client_list_url('Not Interested') }}">Not Interested</a></li>
    </ul>
</li>
<li class="dept-item dept-item--nsd {{ request()->is('sales/pipeline*') ? 'mm-active' : '' }}">
    <a href="{{ route('sales.pipeline') }}" class="waves-effect {{ request()->is('sales/pipeline*') ? 'active' : '' }}">
        <i class="mdi mdi-ray-start-arrow"></i><span>Sales Pipeline</span>
    </a>
</li>
<li class="dept-item dept-item--nsd {{ request()->is('sales/targets*') || request()->is('sales/leaderboard*') ? 'mm-active' : '' }}">
    <a href="{{ route('sales.targets.index') }}" class="waves-effect {{ request()->is('sales/targets*') || request()->is('sales/leaderboard*') ? 'active' : '' }}">
        <i class="mdi mdi-trophy-outline"></i><span>Target & Leaderboard</span>
    </a>
</li>
<li class="dept-item dept-item--nsd {{ request()->is('commercial/engagements*') ? 'mm-active' : '' }}">
    <a href="{{ route('commercial.engagements.index') }}" class="waves-effect {{ request()->is('commercial/engagements*') ? 'active' : '' }}">
        <i class="mdi mdi-file-tree"></i><span>Commercial Orders</span>
    </a>
</li>

{{-- DAY CLOSING --}}
<li class="menu-title menu-title-dept">
    <span class="dept-label">Day Closing</span>
</li>
<li class="dept-item dept-item--main {{ request()->is('day-closing') ? 'mm-active' : '' }}">
    <a class="waves-effect {{ request()->is('day-closing') ? 'active' : '' }}" href="{{ route('day-closing.index') }}">
        <i class="mdi mdi-calendar-check-outline"></i><span>Day Closing</span>
    </a>
</li>
@if ($user->hasRole('Team-Leader'))
<li class="dept-item dept-item--main {{ request()->is('day-closing/approvals*') ? 'mm-active' : '' }}">
    <a class="waves-effect {{ request()->is('day-closing/approvals*') ? 'active' : '' }}" href="{{ route('day-closing.approvals') }}">
        <i class="mdi mdi-checkbox-marked-outline"></i><span>Closing Approvals</span>
    </a>
</li>
@endif
<li class="dept-item dept-item--main {{ request()->is('daily-targets*') ? 'mm-active' : '' }}">
    <a class="waves-effect {{ request()->is('daily-targets*') ? 'active' : '' }}" href="{{ route('daily-targets.index') }}">
        <i class="mdi mdi-target"></i><span>Daily Targets</span>
    </a>
</li>

{{-- HRMS MODULE --}}
<li class="dept-item dept-item--main {{ request()->is('hrms*') ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('hrms*') ? 'active' : '' }}">
        <i class="mdi mdi-account-group-outline"></i><span>HRMS</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ request()->is('hrms*') ? 'true' : 'false' }}">
        <li class="{{ request()->is('hrms/holidays*') ? 'mm-active' : '' }}"><a href="{{ route('hrms.holidays.index') }}">Holiday Calendar</a></li>
        <li class="{{ request()->is('hrms/my-leaves*') ? 'mm-active' : '' }}"><a href="{{ route('hrms.my-leaves.index') }}">My Leaves</a></li>
    </ul>
</li>

{{-- ANALYTICS & REPORTS --}}
<li class="dept-item dept-item--reports {{ request()->is('my-insights*') || request()->is('sales/calendar*') || request()->is('mysts/searchsts*') || request()->is('reports/dsr*') || request()->is('reports/employees*') ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('my-insights*') || request()->is('sales/calendar*') || request()->is('mysts/searchsts*') || request()->is('reports/dsr*') || request()->is('reports/employees*') ? 'active' : '' }}">
        <i class="mdi mdi-chart-bar"></i><span>Analytics & Reports</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ request()->is('my-insights*') || request()->is('sales/calendar*') || request()->is('mysts/searchsts*') || request()->is('reports/dsr*') || request()->is('reports/employees*') ? 'true' : 'false' }}">
        <li class="{{ request()->is('my-insights*') ? 'mm-active' : '' }}"><a href="{{ route('my-insights') }}">My Insights</a></li>
        <li class="{{ request()->is('sales/calendar*') ? 'mm-active' : '' }}"><a href="{{ route('sales.calendar.index') }}">Activity Calendar</a></li>
        <li class="{{ request()->is('mysts/searchsts*') ? 'mm-active' : '' }}"><a href="{{ url('mysts/searchsts') }}">Search STS</a></li>
        <li class="{{ request()->is('reports/dsr/searchdsr*') ? 'mm-active' : '' }}"><a href="{{ url('reports/dsr/searchdsr') }}">DSR Search</a></li>
        <li class="{{ request()->is('reports/dsr/salesreports*') ? 'mm-active' : '' }}"><a href="{{ url('reports/dsr/salesreports') }}">Sales Analytics</a></li>
        @if ($user->hasRole('Team-Leader'))
        <li class="{{ request()->is('reports/employees*') ? 'mm-active' : '' }}"><a href="{{ route('reports.employees') }}">NSD Team Report</a></li>
        @endif
    </ul>
</li>
