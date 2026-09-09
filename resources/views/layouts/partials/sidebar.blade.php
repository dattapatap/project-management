@php
$deptId = optional($user->departments)->department;
$isAdmin = $user->isGlobalAdmin();
$isBm = $user->isBranchManager();
@endphp

<ul class="metismenu list-unstyled" id="side-menu">

    {{-- MAIN HEADER & DASHBOARD --}}
    <li class="menu-title menu-title-dept">
        <span class="dept-label">MENU</span>
    </li>

    <li class="dept-item dept-item--main">
        <a class="waves-effect {{ request()->is('home') ? 'active' : '' }}" href="{{ url('/home') }}">
            <i class="mdi mdi-view-dashboard-outline"></i><span>Dashboard</span>
        </a>
    </li>

    {{-- ROLE-BASED TAILORED SIDEBAR DISPATCHER --}}
    @if ($isAdmin || $isBm)
        {{-- 1. Full Authority Menu for Global Admin & Branch Managers --}}
        @include('layouts.partials.sidebar.admin-menu')
    @elseif ($deptId == 1 || $user->hasRole('Sales-Executive'))
        {{-- 2. Sales (NSD) Workspace --}}
        @include('layouts.partials.sidebar.nsd-menu')
    @elseif ($deptId == 3 || $user->hasRole('CSD-Executive'))
        {{-- 3. Customer Success (CSD) Workspace --}}
        @include('layouts.partials.sidebar.csd-menu')
    @else
        {{-- 4. Operations (OD) Workspace (Developers, Designers, SEO, PMs, TLs, Staff) --}}
        @include('layouts.partials.sidebar.od-menu')
    @endif

</ul>
