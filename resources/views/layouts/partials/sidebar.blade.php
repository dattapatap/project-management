@php
$deptId = optional($user->departments)->department;
$isAdmin = $user->isGlobalAdmin();
$isBm = $user->isBranchManager();
$isAuthority = $user->hasBranchWideAccess();
$isPm = $user->hasRole('Project-Manager');

$showOdProjects = $user->hasRole(['Admin', 'Project-Manager', 'Team-Leader', 'Branch-Manager'])
&& ($isAdmin || $isBm || $deptId == 2);
$showOdEmployee = $user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant']);
$showNsd = $isBm || $isAdmin
|| ($user->hasRole(['Sales-Executive', 'Team-Leader']) && $deptId == 1);
$showCsd = $isBm || $isAdmin
|| ($user->hasRole(['CSD-Executive', 'Team-Leader']) && $deptId == 3);
$showReports = $isAdmin || $isPm || $isBm;
@endphp

<ul class="metismenu list-unstyled" id="side-menu">

    {{-- MAIN --}}
    <li class="menu-title menu-title-dept">
        <span class="dept-label">MENU</span>
    </li>

    <li class="dept-item dept-item--main">
        <a class="waves-effect {{ request()->is('home') ? 'active' : '' }}" href="{{ url('/home') }}">
            <i class="mdi mdi-view-dashboard-outline"></i><span>Dashboard</span>
        </a>
    </li>

    {{-- ADMINISTRATION / BRANCH MANAGEMENT --}}
    @if ($isAuthority)
    <li class="dept-item dept-item--admin {{ request()->is('users*') || request()->is('departments*') ? 'mm-active' : '' }}">
        <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('users*') || request()->is('departments*') ? 'active' : '' }}">
            <i class="mdi mdi-account-settings-outline"></i><span>Users & Teams</span>
        </a>
        <ul class="sub-menu" aria-expanded="{{ request()->is('users*') || request()->is('departments*') ? 'true' : 'false' }}">
            <li class="{{ request()->is('users*') ? 'mm-active' : '' }}"><a href="{{ url('/users') }}">Users</a></li>
            <li class="{{ request()->is('departments*') ? 'mm-active' : '' }}"><a href="{{ url('/departments') }}">Departments</a></li>
        </ul>
    </li>

    <li class="dept-item dept-item--admin {{ request()->is('payments*') ? 'mm-active' : '' }}">
        <a class="waves-effect {{ request()->is('payments*') ? 'active' : '' }}" href="{{ url('/payments') }}">
            <i class="mdi mdi-cash-multiple"></i><span>Payments</span>
        </a>
    </li>
    @endif



    {{-- SALES · NSD --}}
    @if ($showNsd)
    <li class="menu-title menu-title-dept">
        <!-- <span class="dept-badge dept-badge--nsd">NSD</span> -->
        <span class="dept-label">Sales</span>
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
    <li class="dept-item dept-item--nsd {{ request()->is('sales/catalog*') ? 'mm-active' : '' }}">
        <a href="{{ route('sales.catalog.index') }}" class="waves-effect {{ request()->is('sales/catalog*') ? 'active' : '' }}">
            <i class="mdi mdi-book-open-outline"></i><span>Service Catalog</span>
        </a>
    </li>
    <li class="dept-item dept-item--nsd {{ request()->is('sales/targets*') || request()->is('sales/leaderboard*') ? 'mm-active' : '' }}">
        <a href="{{ route('sales.targets.index') }}" class="waves-effect {{ request()->is('sales/targets*') || request()->is('sales/leaderboard*') ? 'active' : '' }}">
            <i class="mdi mdi-trophy-outline"></i><span>Target & Leaderboard</span>
        </a>
    </li>
    <li class="dept-item dept-item--nsd {{ request()->is('sales/calendar*') ? 'mm-active' : '' }}">
        <a href="{{ route('sales.calendar.index') }}" class="waves-effect {{ request()->is('sales/calendar*') ? 'active' : '' }}">
            <i class="mdi mdi-calendar-clock"></i><span>Activity Calendar</span>
        </a>
    </li>
    <li class="dept-item dept-item--nsd {{ request()->is('mysts/searchsts*') ? 'mm-active' : '' }}">
        <a href="{{ url('mysts/searchsts') }}" class="waves-effect {{ request()->is('mysts/searchsts*') ? 'active' : '' }}">
            <i class="mdi mdi-magnify"></i><span>Search STS</span>
        </a>
    </li>
    <li class="dept-item dept-item--nsd {{ request()->is('reports/dsr/*') ? 'mm-active' : '' }}">
        <a href="javascript:void(0);" class="has-arrow waves-effect {{ request()->is('reports/dsr/*') ? 'active' : '' }}">
            <i class="mdi mdi-clipboard-text-outline"></i><span>DSR Reports</span>
        </a>
        <ul class="sub-menu {{ request()->is('reports/dsr/*') ? 'mm-show' : '' }}">
            <li class="{{ request()->is('reports/dsr/searchdsr*') ? 'mm-active' : '' }}"><a href="{{ url('reports/dsr/searchdsr') }}">DSR Search</a></li>
            <li class="{{ request()->is('reports/dsr/salesreports*') ? 'mm-active' : '' }}"><a href="{{ url('reports/dsr/salesreports') }}">Sales Report</a></li>
        </ul>
    </li>
    <li class="dept-item dept-item--nsd {{ request()->is('commercial/engagements*') ? 'mm-active' : '' }}">
        <a href="{{ route('commercial.engagements.index') }}" class="waves-effect {{ request()->is('commercial/engagements*') ? 'active' : '' }}">
            <i class="mdi mdi-file-tree"></i><span>Commercial Orders</span>
        </a>
    </li>
    @if ($user->hasRole('Sales-Executive') || ($user->hasRole('Team-Leader') && $deptId == 1))
    <li class="dept-item dept-item--nsd {{ request()->is('my-insights*') ? 'mm-active' : '' }}">
        <a href="{{ route('my-insights') }}" class="waves-effect {{ request()->is('my-insights*') ? 'active' : '' }}">
            <i class="mdi mdi-chart-line"></i><span>My Insights</span>
        </a>
    </li>
    @endif
    @if (($user->hasRole('Team-Leader') && $deptId == 1) || $isBm || $isAdmin)
    <li class="dept-item dept-item--nsd {{ request()->is('reports/employees*') ? 'mm-active' : '' }}">
        <a href="{{ route('reports.employees') }}" class="waves-effect {{ request()->is('reports/employees*') ? 'active' : '' }}">
            <i class="mdi mdi-account-supervisor"></i><span>NSD Team Report</span>
        </a>
    </li>
    @endif
    @endif


    {{-- OPERATIONS · OD --}}
    @if ($showOdProjects || $showOdEmployee)
    <li class="menu-title menu-title-dept">
        <!-- <span class="dept-badge dept-badge--od">OD</span> -->
        <span class="dept-label">Operations</span>
    </li>
    @if ($showOdProjects)
    <li class="dept-item dept-item--od {{ request()->is('projects') || (request()->is('projects*') && !request()->is('projects/timeline') && !request()->is('projects/resources')) ? 'mm-active' : '' }}">
        <a href="{{ url('/projects') }}" class="waves-effect {{ request()->is('projects') || (request()->is('projects*') && !request()->is('projects/timeline') && !request()->is('projects/resources')) ? 'active' : '' }}">
            <i class="mdi mdi-folder-multiple-outline"></i><span>Projects</span>
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
    @if ($showOdEmployee)
    <li class="dept-item dept-item--od {{ request()->is('projects*') && !request()->is('projects/timeline') && !request()->is('projects/resources') ? 'mm-active' : '' }}">
        <a href="{{ url('/projects') }}" class="waves-effect {{ request()->is('projects*') && !request()->is('projects/timeline') && !request()->is('projects/resources') ? 'active' : '' }}">
            <i class="mdi mdi-briefcase-outline"></i><span>My Projects</span>
        </a>
    </li>
    <li class="dept-item dept-item--od {{ request()->is('my-insights*') ? 'mm-active' : '' }}">
        <a href="{{ route('my-insights') }}" class="waves-effect {{ request()->is('my-insights*') ? 'active' : '' }}">
            <i class="mdi mdi-chart-timeline-variant"></i><span>My Insights</span>
        </a>
    </li>
    @endif
    @endif

    {{-- CUSTOMER SUCCESS · CSD --}}
    @if ($showCsd)
    <li class="menu-title menu-title-dept">
        <!-- <span class="dept-badge dept-badge--csd">CSD</span> -->
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
    @if ($user->hasRole('CSD-Executive') || ($user->hasRole('Team-Leader') && $deptId == 3))
    <li class="dept-item dept-item--csd {{ request()->is('my-insights*') ? 'mm-active' : '' }}">
        <a href="{{ route('my-insights') }}" class="waves-effect {{ request()->is('my-insights*') ? 'active' : '' }}">
            <i class="mdi mdi-chart-areaspline"></i><span>My Insights</span>
        </a>
    </li>
    @endif
    @if (($user->hasRole('Team-Leader') && $deptId == 3) || $isBm)
    <li class="dept-item dept-item--csd {{ request()->is('csd/reports*') ? 'mm-active' : '' }}">
        <a href="{{ route('csd.reports.team') }}" class="waves-effect {{ request()->is('csd/reports*') ? 'active' : '' }}">
            <i class="mdi mdi-account-group"></i><span>CSD Team Report</span>
        </a>
    </li>
    @endif
    @endif

    {{-- REPORTS --}}
    @if ($showReports)
    <li class="menu-title menu-title-dept">
        <!-- <span class="dept-badge dept-badge--reports">RPT</span> -->
        <span class="dept-label">Analytics</span>
    </li>
    @if ($isAuthority)
    <li class="dept-item dept-item--reports {{ request()->is('reports/dsr/searchdsr*') ? 'mm-active' : '' }}">
        <a href="{{ url('reports/dsr/searchdsr') }}" class="waves-effect {{ request()->is('reports/dsr/searchdsr*') ? 'active' : '' }}">
            <i class="mdi mdi-file-document-outline"></i><span>DSR Report</span>
        </a>
    </li>
    <li class="dept-item dept-item--reports {{ request()->is('reports/dsr/salesreports*') ? 'mm-active' : '' }}">
        <a href="{{ url('reports/dsr/salesreports') }}" class="waves-effect {{ request()->is('reports/dsr/salesreports*') ? 'active' : '' }}">
            <i class="mdi mdi-trending-up"></i><span>Sales Analytics</span>
        </a>
    </li>
    @endif
    <li class="dept-item dept-item--reports {{ request()->is('reports/projects*') ? 'mm-active' : '' }}">
        <a href="{{ route('reports.projects') }}" class="waves-effect {{ request()->is('reports/projects*') ? 'active' : '' }}">
            <i class="mdi mdi-folder-multiple-outline"></i><span>Projects</span>
        </a>
    </li>
    <li class="dept-item dept-item--reports {{ request()->is('reports/operations*') ? 'mm-active' : '' }}">
        <a href="{{ route('reports.operations') }}" class="waves-effect {{ request()->is('reports/operations*') ? 'active' : '' }}">
            <i class="mdi mdi-chart-bar"></i><span>Department Reports</span>
        </a>
    </li>
    <li class="dept-item dept-item--reports {{ request()->is('reports/employee*') ? 'mm-active' : '' }}">
        <a href="{{ route('reports.employees') }}" class="waves-effect {{ request()->is('reports/employee*') ? 'active' : '' }}">
            <i class="mdi mdi-account-group-outline"></i><span>Employees</span>
        </a>
    </li>
    @endif

</ul>
