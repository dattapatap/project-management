@php
$isTl = $user->hasRole('Team-Leader');
@endphp

{{-- CUSTOMER SUCCESS · CSD --}}
<li class="menu-title menu-title-dept">
    <span class="dept-label">Customer Success</span>
</li>
<li class="dept-item dept-item--csd {{ request()->is('csd/clients*') ? 'mm-active' : '' }}">
    <a href="{{ route('csd.clients.index') }}" class="waves-effect {{ request()->is('csd/clients*') ? 'active' : '' }}">
        <i class="mdi mdi-account-heart-outline"></i><span>Clients</span>
    </a>
</li>
<li class="dept-item dept-item--csd {{ request()->is('csd/collections*') ? 'mm-active' : '' }}">
    <a href="{{ route('csd.collections.index') }}" class="waves-effect {{ request()->is('csd/collections*') ? 'active' : '' }}">
        <i class="mdi mdi-cash-register"></i><span>Collections</span>
    </a>
</li>
<li class="dept-item dept-item--csd {{ request()->is('csd/communications*') ? 'mm-active' : '' }}">
    <a href="{{ route('csd.communications.index') }}" class="waves-effect {{ request()->is('csd/communications*') ? 'active' : '' }}">
        <i class="mdi mdi-message-text-outline"></i><span>Communications</span>
    </a>
</li>
<li class="dept-item dept-item--csd {{ request()->is('csd/change-requests*') ? 'mm-active' : '' }}">
    <a href="{{ route('csd.change-requests.index') }}" class="waves-effect {{ request()->is('csd/change-requests*') ? 'active' : '' }}">
        <i class="mdi mdi-file-document-edit-outline"></i><span>Change Requests</span>
    </a>
</li>
<li class="dept-item dept-item--csd {{ request()->is('csd/support*') ? 'mm-active' : '' }}">
    <a href="{{ route('csd.support.index') }}" class="waves-effect {{ request()->is('csd/support*') ? 'active' : '' }}">
        <i class="mdi mdi-lifebuoy"></i><span>Support</span>
    </a>
</li>
<li class="dept-item dept-item--csd {{ request()->is('csd/amc*') ? 'mm-active' : '' }}">
    <a href="{{ route('csd.amc.index') }}" class="waves-effect {{ request()->is('csd/amc*') ? 'active' : '' }}">
        <i class="mdi mdi-shield-check-outline"></i><span>AMC Contracts</span>
    </a>
</li>
<li class="dept-item dept-item--csd {{ request()->is('csd/renewals*') ? 'mm-active' : '' }}">
    <a href="{{ route('csd.renewals.index') }}" class="waves-effect {{ request()->is('csd/renewals*') ? 'active' : '' }}">
        <i class="mdi mdi-autorenew"></i><span>Renewals</span>
    </a>
</li>
<li class="dept-item dept-item--csd {{ request()->is('csd/opportunities*') ? 'mm-active' : '' }}">
    <a href="{{ route('csd.opportunities.index') }}" class="waves-effect {{ request()->is('csd/opportunities*') ? 'active' : '' }}">
        <i class="mdi mdi-trending-up"></i><span>Opportunities</span>
    </a>
</li>
<li class="dept-item dept-item--csd {{ request()->is('commercial/engagements*') ? 'mm-active' : '' }}">
    <a href="{{ route('commercial.engagements.index') }}" class="waves-effect {{ request()->is('commercial/engagements*') ? 'active' : '' }}">
        <i class="mdi mdi-file-tree"></i><span>Commercial Orders</span>
    </a>
</li>
<li class="dept-item dept-item--csd {{ request()->is('sales/targets*') || request()->is('sales/leaderboard*') ? 'mm-active' : '' }}">
    <a href="{{ route('sales.targets.index') }}" class="waves-effect {{ request()->is('sales/targets*') || request()->is('sales/leaderboard*') ? 'active' : '' }}">
        <i class="mdi mdi-trophy-outline"></i><span>Target & Leaderboard</span>
    </a>
</li>

{{-- DAY CLOSING --}}
<li class="menu-title menu-title-dept">
    <span class="dept-label">Day Closing</span>
</li>
<li class="dept-item dept-item--main {{ request()->is('day-closing') && !request()->is('day-closing/approvals*') ? 'mm-active' : '' }}">
    <a class="waves-effect {{ request()->is('day-closing') && !request()->is('day-closing/approvals*') ? 'active' : '' }}" href="{{ route('day-closing.index') }}">
        <i class="mdi mdi-calendar-check-outline"></i><span>Day Closing</span>
    </a>
</li>
@if ($isTl)
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
        <li class="{{ request()->is('hrms/my-leaves*') ? 'mm-active' : '' }}"><a href="{{ route('hrms.my-leaves.index') }}">My Leaves</a></li>
    </ul>
</li>

{{-- ANALYTICS & REPORTS --}}
<li class="dept-item dept-item--reports {{ request()->is('my-insights*') || request()->is('csd/reports*') ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('my-insights*') || request()->is('csd/reports*') ? 'active' : '' }}">
        <i class="mdi mdi-chart-bar"></i><span>Analytics & Reports</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ request()->is('my-insights*') || request()->is('csd/reports*') ? 'true' : 'false' }}">
        <li class="{{ request()->is('my-insights*') ? 'mm-active' : '' }}"><a href="{{ route('my-insights') }}">My Insights</a></li>
        @if ($isTl)
        <li class="{{ request()->is('csd/reports*') ? 'mm-active' : '' }}"><a href="{{ route('csd.reports.team') }}">CSD Team Report</a></li>
        @endif
    </ul>
</li>
