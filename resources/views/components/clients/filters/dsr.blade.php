@extends('layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.5);
        --glass-border: rgba(127, 0, 255, 0.08);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.04);
        --primary-gradient: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%);
        --secondary-gradient: linear-gradient(135deg, #1e003c 0%, #3a007d 100%);
        --accent-color: #7F00FF;
    }

    .glass-card-premium {
        background: #fff;
        border: 1px solid rgba(127, 0, 255, 0.08);
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(31, 38, 135, 0.05);
        overflow: hidden;
    }

    /* ── Compact Page Header ── */
    .premium-page-header {
        background: linear-gradient(135deg, #1e003c 0%, #3a007d 100%);
        border-radius: 14px;
        padding: 20px 26px;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        margin-bottom: 22px;
        box-shadow: 0 6px 20px rgba(30, 0, 60, 0.12);
    }

    .premium-page-header h4 { font-size: 17px !important; font-weight: 700 !important; margin-bottom: 2px !important; }
    .premium-page-header p  { font-size: 12px !important; }

    .premium-page-header::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -5%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(225, 0, 255, 0.12) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    /* ── Filter Form ── */
    .form-control-premium {
        border: 1px solid rgba(127, 0, 255, 0.15) !important;
        border-radius: 10px !important;
        padding: 8px 14px !important;
        height: auto !important;
        font-size: 13px !important;
        color: #3d3355 !important;
        background-color: #ffffff !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
    }

    .form-control-premium:focus {
        border-color: #7F00FF !important;
        box-shadow: 0 0 0 3px rgba(127, 0, 255, 0.1) !important;
    }

    .form-group label {
        font-weight: 600;
        color: #4c3c63;
        margin-bottom: 5px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .select2-container--default .select2-selection--single {
        border: 1px solid rgba(127, 0, 255, 0.15) !important;
        border-radius: 10px !important;
        height: 40px !important;
        padding: 6px 12px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
    }

    /* ── DSR Results Table ── */
    .dsr-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }

    .dsr-table thead th {
        background: #f3f0fa !important;
        color: #4c3c63 !important;
        font-weight: 700;
        font-size: 10.5px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 9px 11px !important;
        border-bottom: 2px solid rgba(127, 0, 255, 0.14) !important;
        border-top: none !important;
        white-space: nowrap;
    }

    .dsr-table tbody tr {
        border-bottom: 1px solid #ece8f8;
        transition: background 0.12s ease;
    }

    .dsr-table tbody tr:nth-child(even) { background: #faf8ff; }
    .dsr-table tbody tr:hover { background: #f5f2ff !important; }

    .dsr-table tbody td {
        padding: 8px 11px !important;
        vertical-align: middle !important;
        color: #3d3355;
        font-size: 12.5px;
        border-top: none !important;
        border-bottom: 1px solid #ece8f8 !important;
        line-height: 1.45;
    }

    .dsr-company-name {
        font-weight: 700;
        font-size: 13px;
        color: #2d1f4a;
        display: block;
        line-height: 1.3;
    }

    /* ── Status Badges ── */
    .badge-status-premium {
        padding: 3px 9px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 10px;
        display: inline-block;
        text-transform: capitalize;
        white-space: nowrap;
        line-height: 1.6;
    }

    .badge-status-warm        { background: rgba(253,126,20,0.1)  !important; color: #c96110 !important; }
    .badge-status-hot         { background: rgba(220,53,69,0.1)   !important; color: #c0392b !important; }
    .badge-status-matured     { background: rgba(40,167,69,0.1)   !important; color: #1a7c3e !important; }
    .badge-status-notinterested { background: rgba(108,117,125,0.1) !important; color: #545b62 !important; }
    .badge-status-default     { background: rgba(0,123,255,0.08)  !important; color: #0062cc !important; }

    /* ── Modal ── */
    .modal-premium { border-radius: 18px !important; overflow: hidden; border: none; }

    .modal-header-premium {
        background: var(--secondary-gradient);
        color: #ffffff;
        border-bottom: none;
        padding: 18px 24px;
    }

    .modal-header-premium .modal-title { color: #fff; font-weight: 700; font-size: 15px; }
    .modal-header-premium .close { color: #fff; opacity: 0.8; text-shadow: none; }
    .modal-header-premium .close:hover { opacity: 1; }

    .sts-metric-a {
        color: #7F00FF; font-weight: 700;
        padding: 5px 11px; border-radius: 7px;
        background: rgba(127,0,255,0.06);
        transition: all 0.18s; display: inline-block;
        text-decoration: none !important;
        font-size: 13px;
    }

    .sts-metric-a:hover {
        background: #7F00FF; color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(127,0,255,0.18);
    }

    .table-mysts th {
        color: #4c3c63; font-weight: 700;
        background: #f6f5fc; text-align: center;
        border: 1px solid rgba(127,0,255,0.08) !important;
        padding: 8px 10px; font-size: 11px; text-transform: uppercase;
    }

    .table-mysts td {
        color: #495057; font-weight: 600;
        background: #fff; text-align: center;
        border: 1px solid rgba(127,0,255,0.08) !important;
        padding: 9px 10px; font-size: 12.5px;
    }

    /* ── Action Buttons ── */
    .btn-premium-search {
        background: var(--primary-gradient); color: #fff;
        border: none; font-weight: 600; font-size: 13px;
        padding: 9px 22px; border-radius: 10px;
        box-shadow: 0 4px 14px rgba(127,0,255,0.22);
        transition: all 0.2s ease;
    }

    .btn-premium-search:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(127,0,255,0.32); }

    .btn-premium-outline {
        background: transparent; color: #7F00FF;
        border: 1px solid rgba(127,0,255,0.3);
        font-weight: 600; font-size: 13px;
        padding: 9px 22px; border-radius: 10px;
        transition: all 0.2s ease;
    }

    .btn-premium-outline:hover { background: rgba(127,0,255,0.05); border-color: #7F00FF; color: #7F00FF; transform: translateY(-1px); }

    .btn-premium-solid {
        background: #1e003c; color: #fff;
        border: none; font-weight: 600; font-size: 13px;
        padding: 9px 22px; border-radius: 10px;
        transition: all 0.2s ease;
    }

    .btn-premium-solid:hover { background: #3200a8; color: #fff; transform: translateY(-1px); }

    /* ── DSR separator ── */
    .dsr-divider { border: none; border-top: 1px dashed rgba(127,0,255,0.15); margin: 28px 0; }
</style>
@endsection

@section('content')

<div class="container-fluid">
    <!-- start page title -->
    <div class="premium-page-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div>
            <h4 class="mb-1 text-white font-size-22 font-weight-700">🔍 Search DSR (Daily Sales Report)</h4>
            <p class="mb-0 text-white-50 font-size-13">Filter active presentations, meetings, sales metrics, and download reports.</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex align-items-center">
            <a href="{{ url('/') }}" class="btn btn-light btn-sm mr-3 font-weight-bold d-inline-flex align-items-center" style="border-radius: 8px;">
                <i class="mdi mdi-arrow-left mr-1"></i> Back
            </a>
            <ol class="breadcrumb m-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white-50">{{ env('APP_NAME')}}</a></li>
                <li class="breadcrumb-item active text-white font-weight-600">Search DSR</li>
            </ol>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-lg-12">
            <div class="card glass-card-premium border-0">
                <div class="card-body p-3 p-md-4">
                    <form class="mb-4" id="frm-search-dsr" action="{{ route('report.searchdsr')}}">
                        <div class="row">

                            @if($user->hasRole('Sales-Executive'))
                            <input type="hidden" name="employee" id="employee" value="{{ $user->id}}">
                            @elseif($user->hasRole('Team-Leader'))
                            @php
                            $teams = DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
                            $fltUsers = App\Models\TeamMembers::with('users.roles')
                            ->whereHas('users.roles', function($query){
                            $query->where('name', 'Sales-Executive');
                            })
                            ->whereIn('team', $teams)->where('status', true)->get();
                            @endphp
                            <div class="col-md-3 col-sm-12">
                                <div class="form-group mb-4">
                                    <label class="font-weight-600">Select Executive</label>
                                    <select class="form-control form-control-premium select-premium" name="employee" id="employee" required>
                                        <option value="All">All Team Members</option>
                                        @if($user->hasRole('Team-Leader'))
                                        <option value="{{ $user->id }}" @if( $search && $search['employee']==$user->id ) selected @endif>Self</option>
                                        @endif
                                        @foreach ($fltUsers as $item)
                                        <option value="{{ $item->users->id }}" @if( $search && $search['employee']==$item->users->id ) selected @endif>
                                            {{ $item->users->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @else
                            @php
                            $fltUsers = App\Models\User::whereHas('roles', function($query){
                            $query->whereIn('name', ['Sales-Executive', 'Team-Leader' ]);
                            })
                            ->whereHas('departments', function($query){
                            $query->where('department', 1);
                            })
                            ->get();
                            @endphp
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group mb-4">
                                    <label class="font-weight-600">Sales Executive / Manager</label>
                                    <select class="form-control form-control-premium select-premium" name="employee" id="employee" required>
                                        <option value="All">All Sales Employees</option>
                                        @foreach ($fltUsers as $item)
                                        <option value="{{ $item->id }}" @if( $search && $search['employee']==$item->id ) selected @endif>
                                            {{ $item->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @endif

                            <div class="col-md-4 col-sm-12">
                                <div class="form-group mb-4">
                                    <label class="font-weight-600">DSR Status</label>
                                    <select class="form-control form-control-premium select-premium" name="category" id="category" required>
                                        <option value="All" @if($search && $search['category']=='All' ) selected @endif>All Categories</option>
                                        <option value="Warm Prespective" @if($search && $search['category']=='Warm Prespective' ) selected @endif>Warm Prespective</option>
                                        <option value="Hot Prespective" @if($search && $search['category']=='Hot Prespective' ) selected @endif>Hot Prespective</option>
                                        <option value="Matured" @if($search && $search['category']=='Matured' ) selected @endif>Matured Leads</option>
                                        <option value="Not Interested" @if($search && $search['category']=='Not Interested' ) selected @endif>Not Interested</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <div class="form-group mb-4">
                                    <label class="font-weight-600">DSR Action Date Range</label>
                                    <input type="text" name="from_date" id="from_date" value="@if($search) {{ $search['from_date'] }} @endif"
                                        class="form-control form-control-premium" placeholder="Select timeframe..." required>
                                </div>
                            </div>
                            <input type="hidden" name="searchCategory" value="DSR">

                        </div>
                        <div class="row">
                            <div class="col-12 mt-2">
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <button type="submit" class="btn btn-premium-search px-4 py-2-5">
                                        <i class="mdi mdi-magnify mr-1"></i> Search DSR
                                    </button>
                                    @if(!$clients->isEmpty())
                                    <button type="button" class="btn btn-premium-outline btn-dsr-export py-2-5">
                                        <i class="mdi mdi-file-excel-outline mr-1"></i> Export DSR
                                    </button>
                                    @endif
                                    @if(!$user->hasRole('Admin'))
                                    <button type="button" class="btn btn-premium-solid btn-my-sts-count py-2-5 ml-auto">
                                        <i class="mdi mdi-chart-donut mr-1"></i> My DSR Summary
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>

                    <hr class="dsr-divider">

                    @if(!$clients->isEmpty())
                    <div class="table-responsive">
                        <table class="dsr-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 5%">Sl</th>
                                    <th style="width: 20%">Company Name</th>
                                    @if($user->hasRole(["Admin","Team-Leader"]))
                                    <th style="width: 15%">Referral</th>
                                    @endif
                                    <th style="width: 15%">Status</th>
                                    <th class="text-center" style="width: 15%">History Dt.</th>
                                    <th style="width: 20%">Latest Remarks</th>
                                    <th style="width: 12%" class="text-center">TBRO Date</th>
                                    <th style="width: 5%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($clients as $key=>$items)
                                @php
                                $statusLower = strtolower(str_replace(' ', '', $items->status));
                                $badgeClass = 'badge-status-default';
                                if ($statusLower == 'warmprespective') $badgeClass = 'badge-status-warm';
                                if ($statusLower == 'hotprespective') $badgeClass = 'badge-status-hot';
                                if ($statusLower == 'matured') $badgeClass = 'badge-status-matured';
                                if ($statusLower == 'notinterested') $badgeClass = 'badge-status-notinterested';
                                @endphp
                                <tr>
                                    <td class="font-weight-600 text-muted"> {{ ($clients->currentpage()-1) * $clients->perpage() + $key + 1 }} </td>
                                    <td>
                                        <span class="dsr-company-name">{{ $items->name }}</span>
                                    </td>
                                    @if($user->hasRole(["Admin","Team-Leader"]))
                                    <td>
                                        <span class="badge badge-soft-primary px-2 py-1" style="border-radius: 6px;">
                                            {{ $items->referral->name ?? 'Unassigned' }}
                                        </span>
                                    </td>
                                    @endif

                                    <td>
                                        <span class="badge-status-premium {{ $badgeClass }}">
                                            {{ $items->status }}
                                        </span>
                                    </td>
                                    <td class="text-center font-size-13 text-muted">
                                        @if($items->history)
                                        <i class="mdi mdi-calendar-clock text-primary"></i>
                                        {{ Carbon\Carbon::parse($items->history->created_at)->format('d M Y') }}
                                        <span class="d-block font-size-11 text-muted">{{ $items->history->time ? Carbon\Carbon::parse($items->history->time)->format('h:i A') : '' }}</span>
                                        @else
                                        ---
                                        @endif
                                    </td>
                                    <td class="text-muted font-size-13">
                                        {{ $items->history->remarks ?? 'No remarks registered.' }}
                                    </td>
                                    <td class="text-center font-weight-600 font-size-13">
                                        @if($items->history && $items->history->tbro)
                                        <span class="badge badge-soft-warning px-2 py-1" style="border-radius: 6px;">
                                            <i class="mdi mdi-clock-outline mr-1"></i>{{ Carbon\Carbon::parse($items->history->tbro)->format('d M Y') }}
                                        </span>
                                        @else
                                        <span class="text-muted font-weight-400">---</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <a type="button" class="btn btn-outline-primary btn-sm btn-rounded" target="_blank" href="{{ url('clients/'.base64_encode($items->id).'/'.'sts' ) }}"
                                            data-toggle="tooltip" data-placement="bottom" title="Update STS" style="border-radius: 50%; width: 34px; height: 34px; padding: 6px 0;">
                                            <i class="mdi mdi-plus"></i>
                                        </a>
                                    </td>

                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <img src="{{ asset('assets/images/no-data.png') }}" alt="" style="max-height: 80px;" class="mb-3 d-block mx-auto">
                                        <span class="text-muted font-weight-500 font-size-15">No search DSR records exist for selected inputs.</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 d-flex justify-content-end">
                            {{ $clients->links("pagination::bootstrap-4") }}
                        </div>
                    </div>
                    @else
                    <div class="row mt-5">
                        <div class="col-12 text-center py-5">
                            <i class="mdi mdi-alert-circle-outline text-muted" style="font-size: 56px;"></i>
                            <h5 class="text-muted font-weight-600 mt-3 font-size-16">NO DSR LEADS FOUND</h5>
                            <p class="text-muted-50 font-size-13 max-width-320 mx-auto">Try adjusting your executive selection, date timeframe, or status categories to search again.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
</div>

<div id="mySTS" class="modal fade bs-example-modal-xl" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content modal-premium">
            <div class="modal-header modal-header-premium">
                <h5 class="modal-title font-weight-700 m-0" id="myExtraLargeModalLabel">✨ MY DSR Performance Summary</h5>
                <button type="button" class="close mdlClose text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 p-md-5">

                <div class="col-12 sts_report">
                    <div class="table-responsive">
                        <table class="table table-centered mb-0 table-mysts">
                            <thead>
                                <tr>
                                    <th rowspan="2">Emp Id</th>
                                    <th rowspan="2"> Name </th>
                                    <th rowspan="2">STS</th>
                                    <th rowspan="2">UnTouch</th>
                                    <th rowspan="2">Touch</th>
                                    <th colspan="4" style="background: rgba(40,167,69,0.06); color: #28a745;">MET PIPELINE</th>
                                    <th colspan="4" style="background: rgba(127,0,255,0.06); color: #7F00FF;">NOT MET PIPELINE</th>
                                </tr>
                                <tr>
                                    <th style="background: rgba(40,167,69,0.02);"> MET </th>
                                    <th style="background: rgba(40,167,69,0.02);"> Matured</th>
                                    <th style="background: rgba(40,167,69,0.02);"> TBRO </th>
                                    <th style="background: rgba(40,167,69,0.02);"> Remi </th>
                                    <th style="background: rgba(127,0,255,0.02);"> NOT MET </th>
                                    <th style="background: rgba(127,0,255,0.02);"> TBRO</th>
                                    <th style="background: rgba(127,0,255,0.02);"> Remi </th>
                                    <th style="background: rgba(127,0,255,0.02);"> Meet Fxd. </th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Guidance Placeholder -->
                <div id="mysts-placeholder" class="col-12 mt-5 p-5 text-center" style="background: rgba(127, 0, 255, 0.015); border: 1px dashed rgba(127, 0, 255, 0.2); border-radius: 16px;">
                    <i class="mdi mdi-gesture-tap text-primary mb-3" style="font-size: 36px; display: block;"></i>
                    <h5 class="text-premium-dark font-size-15 font-weight-700">Interactive Metrics Segment</h5>
                    <p class="text-muted font-size-13 mb-0 max-width-400 mx-auto">Click on any underlined purple metric count in the performance grid above to instantly load and filter the matching clients database list here!</p>
                </div>

                <div class="content-filtermysts col-12 mt-5 d-none p-4" style="background: #fdfbff; border: 1px solid rgba(127, 0, 255, 0.08); border-radius: 16px;">
                    <h5 class="text-premium-dark font-size-15 font-weight-700 mb-4 d-flex align-items-center">
                        <i class="mdi mdi-filter-outline text-primary mr-2"></i> Filtered Segment Results
                    </h5>
                    <table id="datatable" class="table table-premium table-centered table-striped dt-responsive nowrap"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Company</th>
                                <th>Contact Info</th>
                                <th>Mobile</th>
                                <th>Status</th>
                                <th>TBRO/Meet Fxd. Dt</th>
                                <th>STS Update</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>

                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script type="text/javascript" src="{{ asset('assets/js/moment.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/datepicket.min.js') }}"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}"></script>

<script>
    $(function() {
        var start = moment();
        var end = moment();

        function cb(start, end) {
            $('#from_date').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
        }

        $('#from_date').daterangepicker({
            startDate: start,
            endDate: end,
            maxDate: end,
            ranges: {
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            },
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        }, cb);
    });

    $(document).ready(function() {
        $("#mySTS").modal({
            show: false,
            backdrop: 'static'
        });
        $('.mdlClose').click(function() {
            $("#mySTS").modal('hide');
        });
        $('.btn-my-sts-count').click(function() {
            $('.table-mysts tbody').empty();
            $('#mysts-placeholder').removeClass('d-none');
            $('.content-filtermysts').addClass('d-none');
            $.ajax({
                type: 'GET',
                url: "{{ route('report.get-count-my-sts') }}",
                beforeSend: function($e) {
                    $('.btn-my-sts-count').prop('disabled', true);
                },
                success: function(response) {
                    $('.btn-my-sts-count').prop('disabled', false);
                    if (response.status) {
                        let user = response.user;
                        let sts = response.data;

                        let txtTr = '<tr>' +
                            '<td >' + user.code + '</td>' +
                            '<td class="font-weight-700">' + user.name + '</td>' +
                            '<td ><a href="javascript:void(0);" class="sts-metric-a mysts_client" category="sts" usercode="' + user.id + '" >' + sts.sts + '</a></td>' +
                            '<td > <a href="javascript:void(0);" class="sts-metric-a mysts_client" category="untouch" usercode="' + user.id + '" >' + sts.unTouch + '</a></td>' +
                            '<td > <a href="javascript:void(0);" class="sts-metric-a mysts_client" category="touch" usercode="' + user.id + '" >' + sts.touch + '</a></td>' +
                            '<td > <a href="javascript:void(0);" class="sts-metric-a mysts_client" category="dsrMet" usercode="' + user.id + '" >' + sts.dsrMet + '</a></td>' +
                            '<td > <a href="javascript:void(0);" class="sts-metric-a mysts_client" category="dsrMatured" usercode="' + user.id + '" >' + sts.dsrMatured + '</a></td>' +
                            '<td > <a href="javascript:void(0);" class="sts-metric-a mysts_client" category="dsrTbro" usercode="' + user.id + '" >' + sts.dsrTbro + '</a></td>' +
                            '<td > <a href="javascript:void(0);" class="sts-metric-a mysts_client" category="dsrReminder" usercode="' + user.id + '" >' + sts.dsrReminder + '</a></td>' +

                            '<td > <a href="javascript:void(0);" class="sts-metric-a mysts_client" category="stsNotMet" usercode="' + user.id + '" >' + sts.stsNotMet + '</a></td>' +
                            '<td > <a href="javascript:void(0);" class="sts-metric-a mysts_client" category="stsTbro" usercode="' + user.id + '" >' + sts.stsTBRO + '</a></td>' +
                            '<td > <a href="javascript:void(0);" class="sts-metric-a mysts_client" category="stsReminder" usercode="' + user.id + '" >' + sts.stsReminder + '</a></td>' +
                            '<td > <a href="javascript:void(0);" class="sts-metric-a mysts_client" category="stsMeetFixed" usercode="' + user.id + '" >' + sts.stsMeetingFixed + '</a></td>' +
                            '</tr>';

                        $('.table-mysts tbody').append(txtTr);
                        $('#mySTS').modal('show');
                    } else {
                        $('.table-mysts tbody').append('<tr><td colspan="13"> NO STS RECORDS EXIST!</td></tr>');
                        $('#mySTS').modal('show');
                    }
                },
            });
        });

        $(document).on('click', '.mysts_client', function() {
            $('#mysts-placeholder').addClass('d-none');
            $('.content-filtermysts').addClass('d-none');
            let category = $(this).attr('category');
            let usercode = $(this).attr('usercode');
            $("#datatable").DataTable({
                processing: true,
                serverSide: true,
                bDestroy: true,
                pageLength: 5,
                lengthMenu: [5, 10, 20, 50],
                ajax: {
                    type: 'GET',
                    data: {
                        'category': category,
                        'usercode': usercode
                    },
                    url: "{{ route('report.get-count-by-category') }}",
                    error: function(err) {
                        console.log(err);
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'Sl No',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'contactinfo',
                        name: 'cont_person',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'mobile',
                        name: 'mobile',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'tbro',
                        name: 'history.tbro',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]

            });

            $('.content-filtermysts').removeClass('d-none');

        })

    });

    $(document).ready(function() {
        $('.btn-dsr-export').click(function(e) {
            e.preventDefault();
            $.ajax({
                type: 'GET',
                url: base_url + '/exportsts',
                data: $('#frm-search-dsr').serialize(),
                cache: false,
                contentType: false,
                processData: false,
                xhrFields: {
                    responseType: 'blob',
                },
                beforeSend: function() {
                    $(".btn-dsr-export").html('Exporting..');
                    $(".btn-dsr-export").prop('disabled', true);
                },
                success: function(result, status, xhr) {
                    $(".btn-dsr-export").prop('disabled', false);
                    $(".btn-dsr-export").html('Export DSR');
                    console.log(result);
                    let disposition = xhr.getResponseHeader('content-disposition');
                    let matches = /"([^"]*)"/.exec(disposition);
                    let filename = (matches != null && matches[1] ? matches[1] : 'dsr_list.xlsx');

                    // The actual download
                    var blob = new Blob([result], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },
                error: function(response) {
                    $(".btn-dsr-export").prop('disabled', false);
                    $(".btn-dsr-export").html('Export DSR');
                }
            });
        });
    })
</script>

@endsection
