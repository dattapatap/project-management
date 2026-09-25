{{-- ================================================== --}}
{{-- 1. ADMINISTRATION                                  --}}
{{-- ================================================== --}}
<li class="dept-item dept-item--admin {{ request()->is('users*') || request()->is('departments*') || request()->is('payments*') ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('users*') || request()->is('departments*') || request()->is('payments*') ? 'active' : '' }}">
        <i class="mdi mdi-account-settings-outline"></i><span>Administration</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ request()->is('users*') || request()->is('departments*') || request()->is('payments*') ? 'true' : 'false' }}">
        <li class="{{ request()->is('users*') ? 'mm-active' : '' }}"><a href="{{ url('/users') }}">Users</a></li>
        <li class="{{ request()->is('departments*') ? 'mm-active' : '' }}"><a href="{{ url('/departments') }}">Departments</a></li>
        <li class="{{ request()->is('payments*') ? 'mm-active' : '' }}"><a href="{{ url('/payments') }}">Payments</a></li>
    </ul>
</li>

{{-- ================================================== --}}
{{-- 2. SALES (NSD)                                     --}}
{{-- ================================================== --}}
<li class="dept-item dept-item--nsd {{ request()->is('clients*') || is_client_list_route() || request()->is('sales/catalog*') || request()->is('sales/targets*') || request()->is('sales/leaderboard*') || request()->is('commercial/engagements*') ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('clients*') || is_client_list_route() || request()->is('sales/catalog*') || request()->is('sales/targets*') || request()->is('sales/leaderboard*') || request()->is('commercial/engagements*') ? 'active' : '' }}">
        <i class="mdi mdi-trending-up"></i><span>Sales (NSD)</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ request()->is('clients*') || is_client_list_route() || request()->is('sales/catalog*') || request()->is('sales/targets*') || request()->is('sales/leaderboard*') || request()->is('commercial/engagements*') ? 'true' : 'false' }}">
        <li class="{{ request()->is('clients*') || is_client_list_route() ? 'mm-active' : '' }}"><a href="{{ route('clients.index') }}">Companies</a></li>
        <li class="{{ request()->is('sales/targets*') || request()->is('sales/leaderboard*') ? 'mm-active' : '' }}"><a href="{{ route('sales.targets.index') }}">Target & Leaderboard</a></li>
        <li class="{{ request()->is('commercial/engagements*') ? 'mm-active' : '' }}"><a href="{{ route('commercial.engagements.index') }}">Commercial Orders</a></li>
    </ul>
</li>

{{-- ================================================== --}}
{{-- 3. OPERATIONS (OD)                                 --}}
{{-- ================================================== --}}
<li class="dept-item dept-item--od {{ (request()->is('projects*') && !request()->is('reports/projects*')) || request()->is('projects/timeline*') || request()->is('projects/resources*') ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ (request()->is('projects*') && !request()->is('reports/projects*')) || request()->is('projects/timeline*') || request()->is('projects/resources*') ? 'active' : '' }}">
        <i class="mdi mdi-folder-multiple-outline"></i><span>Operations (OD)</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ (request()->is('projects*') && !request()->is('reports/projects*')) || request()->is('projects/timeline*') || request()->is('projects/resources*') ? 'true' : 'false' }}">
        <li class="{{ request()->is('projects') || (request()->is('projects*') && !request()->is('projects/timeline') && !request()->is('projects/resources') && !request()->is('projects/tasks*')) ? 'mm-active' : '' }}"><a href="{{ url('/projects') }}">Projects List</a></li>
        <li class="{{ request()->is('projects/tasks*') ? 'mm-active' : '' }}"><a href="{{ route('tasks.index') }}">Tasks List</a></li>
        <li class="{{ request()->is('projects/timeline*') ? 'mm-active' : '' }}"><a href="{{ route('projects.timeline') }}">Gantt Timeline</a></li>
        <li class="{{ request()->is('projects/resources*') ? 'mm-active' : '' }}"><a href="{{ route('projects.resources') }}">Resource Allocation</a></li>
    </ul>
</li>

{{-- ================================================== --}}
{{-- 4. CUSTOMER SUCCESS (CSD)                          --}}
{{-- ================================================== --}}
<li class="dept-item dept-item--csd {{ request()->is('csd*') ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('csd*') ? 'active' : '' }}">
        <i class="mdi mdi-account-heart-outline"></i><span>Customer Success</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ request()->is('csd*') ? 'true' : 'false' }}">
        <li class="{{ request()->is('csd/clients*') ? 'mm-active' : '' }}"><a href="{{ route('csd.clients.index') }}">Clients</a></li>
        <li class="{{ request()->is('csd/collections*') ? 'mm-active' : '' }}"><a href="{{ route('csd.collections.index') }}">Collections</a></li>
        <li class="{{ request()->is('csd/communications*') ? 'mm-active' : '' }}"><a href="{{ route('csd.communications.index') }}">Communications</a></li>
        <li class="{{ request()->is('csd/change-requests*') ? 'mm-active' : '' }}"><a href="{{ route('csd.change-requests.index') }}">Change Requests</a></li>
        <li class="{{ request()->is('csd/support*') ? 'mm-active' : '' }}"><a href="{{ route('csd.support.index') }}">Support</a></li>
        <li class="{{ request()->is('csd/amc*') ? 'mm-active' : '' }}"><a href="{{ route('csd.amc.index') }}">AMC Contracts</a></li>
        <li class="{{ request()->is('csd/renewals*') ? 'mm-active' : '' }}"><a href="{{ route('csd.renewals.index') }}">Renewals</a></li>
        <li class="{{ request()->is('csd/opportunities*') ? 'mm-active' : '' }}"><a href="{{ route('csd.opportunities.index') }}">Opportunities</a></li>
    </ul>
</li>

{{-- ================================================== --}}
{{-- 5. DAY CLOSING & TARGETS                           --}}
{{-- ================================================== --}}
<li class="dept-item dept-item--main {{ request()->is('day-closing/approvals*') || request()->is('daily-targets*') ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('day-closing/approvals*') || request()->is('daily-targets*') ? 'active' : '' }}">
        <i class="mdi mdi-checkbox-marked-outline"></i><span>Day Closing</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ request()->is('day-closing/approvals*') || request()->is('daily-targets*') ? 'true' : 'false' }}">
        <li class="{{ request()->is('day-closing/approvals*') ? 'mm-active' : '' }}"><a href="{{ route('day-closing.approvals') }}">Closing Approvals</a></li>
        <li class="{{ request()->is('daily-targets*') ? 'mm-active' : '' }}"><a href="{{ route('daily-targets.index') }}">Daily Targets</a></li>
    </ul>
</li>


{{-- ================================================== --}}
{{-- 7. HRMS SUITE                                      --}}
{{-- ================================================== --}}
<li class="dept-item dept-item--main {{ request()->is('hrms*') || request()->is('admin/attendances*') ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('hrms*') || request()->is('admin/attendances*') ? 'active' : '' }}">
        <i class="mdi mdi-account-group-outline"></i><span>HRMS</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ request()->is('hrms*') || request()->is('admin/attendances*') ? 'true' : 'false' }}">
        <li class="{{ request()->is('hrms/celebrations*') ? 'mm-active' : '' }}"><a href="{{ route('hrms.celebrations.index') }}">Celebrations 🎂</a></li>
        <li class="{{ request()->is('hrms/holidays*') ? 'mm-active' : '' }}"><a href="{{ route('hrms.holidays.index') }}">Holiday Calendar</a></li>
        <li class="{{ request()->is('hrms/announcements*') ? 'mm-active' : '' }}"><a href="{{ route('hrms.announcements.index') }}">Announcements 📢</a></li>
        <li class="{{ request()->is('admin/attendances*') ? 'mm-active' : '' }}"><a href="{{ route('admin.attendances.index') }}">Attendances</a></li>
        <li class="{{ request()->is('hrms/leaves/approvals*') ? 'mm-active' : '' }}"><a href="{{ route('hrms.my-leaves.approvals') }}">Leave Approvals</a></li>
        <li class="{{ request()->is('hrms/attendance-export*') ? 'mm-active' : '' }}"><a href="{{ route('hrms.attendance-export.index') }}">Export Attendance</a></li>
        @if (!$isAdmin)
        <li class="{{ request()->is('hrms/my-leaves*') && !request()->is('hrms/leaves/approvals*') ? 'mm-active' : '' }}"><a href="{{ route('hrms.my-leaves.index') }}">My Leaves</a></li>
        @endif
    </ul>
</li>

{{-- ================================================== --}}
{{-- 6. ANALYTICS & REPORTS                             --}}
{{-- ================================================== --}}
<li class="dept-item dept-item--reports {{ request()->is('mysts/searchsts*') || request()->is('reports/dsr*') || request()->is('reports/projects*') || request()->is('reports/operations*') || request()->is('reports/employee*') ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('mysts/searchsts*') || request()->is('reports/dsr*') || request()->is('reports/projects*') || request()->is('reports/operations*') || request()->is('reports/employee*') ? 'active' : '' }}">
        <i class="mdi mdi-chart-bar"></i><span>Analytics & Reports</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ request()->is('mysts/searchsts*') || request()->is('reports/dsr*') || request()->is('reports/projects*') || request()->is('reports/operations*') || request()->is('reports/employee*') ? 'true' : 'false' }}">
        <li class="{{ request()->is('mysts/searchsts*') ? 'mm-active' : '' }}"><a href="{{ url('mysts/searchsts') }}">Search STS</a></li>
        <li class="{{ request()->is('reports/dsr/searchdsr*') ? 'mm-active' : '' }}"><a href="{{ url('reports/dsr/searchdsr') }}">DSR Search</a></li>
        <li class="{{ request()->is('reports/dsr/salesreports*') ? 'mm-active' : '' }}"><a href="{{ url('reports/dsr/salesreports') }}">Sales Analytics</a></li>
        <li class="{{ request()->is('reports/projects*') ? 'mm-active' : '' }}"><a href="{{ route('reports.projects') }}">Project Reports</a></li>
        <li class="{{ request()->is('reports/operations*') ? 'mm-active' : '' }}"><a href="{{ route('reports.operations') }}">Department Reports</a></li>
        <li class="{{ request()->is('reports/employee*') ? 'mm-active' : '' }}"><a href="{{ route('reports.employees') }}">Employee Performance</a></li>
    </ul>
</li>

{{-- ================================================== --}}
{{-- 8. SETTINGS                                        --}}
{{-- ================================================== --}}
<li class="dept-item dept-item--main {{ request()->is('settings/holidays*') || request()->is('settings/leave-types*') || request()->is('settings/project-categories*') ? 'mm-active' : '' }}">
    <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('settings/holidays*') || request()->is('settings/leave-types*') || request()->is('settings/project-categories*') ? 'active' : '' }}">
        <i class="mdi mdi-cogs"></i><span>Settings</span>
    </a>
    <ul class="sub-menu" aria-expanded="{{ request()->is('settings/holidays*') || request()->is('settings/leave-types*') || request()->is('settings/project-categories*') ? 'true' : 'false' }}">
        <li class="{{ request()->is('settings/holidays*') ? 'mm-active' : '' }}"><a href="{{ route('settings.holidays.index') }}">Holidays</a></li>
        <li class="{{ request()->is('settings/leave-types*') ? 'mm-active' : '' }}"><a href="{{ route('settings.leave-types.index') }}">Leave Types</a></li>
        <li class="{{ request()->is('settings/project-categories*') ? 'mm-active' : '' }}"><a href="{{ route('settings.project-categories.index') }}">Project Categories</a></li>
    </ul>
</li>
