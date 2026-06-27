@extends('layouts.app')

@section('styles')
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.5);
        --glass-border: rgba(127, 0, 255, 0.08);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.04);
        --primary-gradient: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%);
        --secondary-gradient: linear-gradient(135deg, #1e003c 0%, #3a007d 100%);
    }

    /* Glassmorphic Container styling */
    .glass-card-premium {
        background: var(--glass-bg);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: var(--glass-shadow);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }

    .glass-card-premium:hover {
        box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.07);
    }

    /* Page Header */
    .premium-page-header {
        background: linear-gradient(135deg, #0e0021 0%, #1c003d 100%);
        border-radius: 20px;
        padding: 35px 30px;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        margin-bottom: 30px;
        box-shadow: 0 10px 25px rgba(30, 0, 60, 0.15);
    }

    .premium-page-header::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(225, 0, 255, 0.12) 0%, transparent 70%);
        border-radius: 50%;
    }

    /* Table Custom Styles */
    .table-premium {
        border-collapse: separate;
        border-spacing: 0 10px;
        width: 100%;
    }

    .table-premium thead th {
        background-color: #f6f5fc !important;
        border: none !important;
        color: #4c3c63 !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.6px;
        padding: 16px 20px !important;
        text-align: left;
    }

    .table-premium tbody tr.client-main-row {
        background-color: #ffffff;
        box-shadow: 0 3px 8px rgba(127, 0, 255, 0.02);
        transition: all 0.25s ease;
    }

    .table-premium tbody tr.client-main-row:hover {
        background-color: #fdfbff !important;
        box-shadow: 0 5px 15px rgba(127, 0, 255, 0.06);
    }

    .table-premium tbody td {
        border: none !important;
        padding: 18px 20px !important;
        vertical-align: middle !important;
        color: #495057;
        font-size: 14px;
        text-align: left;
    }

    .table-premium tbody tr.client-main-row td:first-child {
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    .table-premium tbody tr.client-main-row td:last-child {
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    /* Expanding Details Row */
    .expandable-details-row {
        display: none;
        background-color: #fcfbfe;
    }

    .expandable-details-row td {
        padding: 0 !important;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        overflow: hidden;
    }

    .expanded-inner-card {
        padding: 24px 30px;
        background: linear-gradient(180deg, #fdfbff 0%, #f7f5fa 100%);
        border-left: 4px solid #7F00FF;
        box-shadow: inset 0 3px 10px rgba(0, 0, 0, 0.02);
    }

    /* Inner Project Elements */
    .inner-project-header {
        font-size: 14px;
        font-weight: 700;
        color: #3b2258;
        letter-spacing: 0.5px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
    }

    .project-mini-table {
        width: 100%;
        background-color: #ffffff;
        border-radius: 12px;
        border: 1px solid rgba(127, 0, 255, 0.06);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.01);
    }

    .project-mini-table th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6c757d;
        background-color: #faf9fc;
        padding: 12px 16px;
        border-bottom: 1px solid rgba(127, 0, 255, 0.06);
    }

    .project-mini-table td {
        padding: 12px 16px !important;
        font-size: 13px;
        border-bottom: 1px solid rgba(127, 0, 255, 0.03) !important;
    }

    .project-mini-table tr:last-child td {
        border-bottom: none !important;
    }

    /* Custom Badges */
    .badge-matured-date {
        background-color: rgba(40, 167, 69, 0.08) !important;
        color: #28a745 !important;
        font-weight: 600;
        font-size: 13px;
        padding: 6px 12px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
    }

    .badge-project-count {
        background: rgba(127, 0, 255, 0.08);
        color: #7F00FF;
        font-weight: 700;
        font-size: 13px;
        padding: 6px 14px;
        border-radius: 30px;
        display: inline-block;
        border: 1px solid rgba(127, 0, 255, 0.12);
    }

    .badge-status-p {
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 11px;
        text-transform: capitalize;
    }

    /* Progress bar style */
    .progress-premium {
        height: 6px;
        border-radius: 10px;
        background-color: #eeeeee;
        overflow: hidden;
    }

    .progress-premium-bar {
        height: 100%;
        border-radius: 10px;
        background: var(--primary-gradient);
    }

    /* Toggle Action Button */
    .btn-toggle-expand {
        background-color: #7F00FF;
        color: #ffffff;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s ease;
        box-shadow: 0 4px 10px rgba(127, 0, 255, 0.2);
    }

    .btn-toggle-expand:hover {
        background-color: #6300c8;
        transform: scale(1.05);
    }

    .btn-toggle-expand.active {
        background-color: #1e003c;
        transform: rotate(180deg);
        box-shadow: 0 4px 10px rgba(30, 0, 60, 0.25);
    }
</style>
@endsection

@section('content')

<div class="container-fluid">
    <!-- start page title -->
    <div class="premium-page-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div>
            <h4 class="mb-1 text-white font-size-22 font-weight-700">🏆 Matured Clients & Project Progress Ledger</h4>
            <p class="mb-0 text-white-50 font-size-13">Direct sales closures, ongoing software/service configurations, and system-wide pipelines.</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex align-items-center">
            <a href="{{ url('/') }}" class="btn btn-light btn-sm mr-3 font-weight-bold d-inline-flex align-items-center" style="border-radius: 8px;">
                <i class="mdi mdi-arrow-left mr-1"></i> Back
            </a>
            <ol class="breadcrumb m-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white-50">{{ env('APP_NAME')}}</a></li>
                <li class="breadcrumb-item active text-white font-weight-600">Matured Clients</li>
            </ol>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-lg-12">
            <div class="card glass-card-premium border-0">
                <div class="card-body p-4 p-md-5">

                    <div class="table-responsive">
                        <table class="table table-premium mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 5%">Sl</th>
                                    <th style="width: 20%">Company Details</th>
                                    <th style="width: 15%">Contact Details</th>
                                    @if(\Illuminate\Support\Facades\Auth::user()->hasRole(['Admin', 'Team-Leader', 'Branch-Manager']))
                                    <th style="width: 15%">Created By</th>
                                    <th style="width: 15%">Following By</th>
                                    @endif
                                    <th style="width: 10%" class="text-center">Matured Date</th>
                                    <th style="width: 10%" class="text-center">Active Projects</th>
                                    <th style="width: 10%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($clients as $key => $items)
                                <tr class="client-main-row" data-client-id="{{ $items->id }}">
                                    <td class="font-weight-600 text-muted">
                                        {{ ($clients->currentpage()-1) * $clients->perpage() + $key + 1 }}
                                    </td>
                                    <td>
                                        <h5 class="font-size-15 font-weight-700 text-premium-dark mb-1">{{ $items->name }}</h5>
                                        <span class="badge badge-soft-primary px-2 py-1" style="border-radius: 6px;">
                                            <i class="mdi mdi-tag-outline mr-1"></i>{{ $items->category ?? 'Enterprise client' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="font-weight-600 mb-1" style="color: #495057;">
                                            {{ $items->cont_person }}
                                            @if($items->designation)
                                            <span class="font-size-12 text-muted font-weight-400">({{ $items->designation }})</span>
                                            @endif
                                        </div>
                                        <div class="font-size-12 text-muted d-flex align-items-center">
                                            <i class="mdi mdi-phone-outline text-primary mr-1"></i> {{ $items->mobile ?? '---' }}
                                        </div>
                                    </td>
                                    @if(\Illuminate\Support\Facades\Auth::user()->hasRole(['Admin', 'Team-Leader', 'Branch-Manager']))
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-account-plus-outline text-muted mr-1.5 font-size-16"></i>
                                            <div>
                                                <span class="font-size-13 font-weight-bold d-block text-dark">
                                                    {{ $items->creator ? explode(' ', $items->creator->name)[0] : 'System' }}
                                                </span>
                                                <small class="text-muted">
                                                    {{ $items->created_at ? $items->created_at->format('d M Y') : '---' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-account-arrow-right-outline text-primary mr-1.5 font-size-16"></i>
                                            <div>
                                                <span class="font-size-13 font-weight-bold d-block text-dark">
                                                    {{ $items->referral ? explode(' ', $items->referral->name)[0] : '---' }}
                                                </span>
                                                <small class="text-muted">Assigned</small>
                                            </div>
                                        </div>
                                    </td>
                                    @endif
                                    <td class="text-center">
                                        <span class="badge-matured-date">
                                            <i class="mdi mdi-shield-check-outline mr-1"></i>
                                            @if($items->active_from)
                                            {{ \Carbon\Carbon::parse($items->active_from)->format('d M Y') }}
                                            @else
                                            Not Specified
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-project-count">
                                            <i class="mdi mdi-briefcase-outline mr-1"></i> {{ count($items->projects) }} Projects
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <!-- Expand toggle button -->
                                            <button type="button" class="btn-toggle-expand btn-trigger-expand" data-toggle="tooltip" title="View Projects Info">
                                                <i class="mdi mdi-chevron-down font-size-18"></i>
                                            </button>

                                            <!-- Link to standard STS details if necessary -->
                                            <a class="btn btn-outline-primary btn-sm btn-rounded" target="_blank" href="{{ url('clients/'.base64_encode($items->id).'/'.'sts' ) }}"
                                                data-toggle="tooltip" title="Update Client Pipeline" style="border-radius: 50%; width: 32px; height: 32px; padding: 6px 0;">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Expandable Row for Projects -->
                                <tr class="expandable-details-row" id="expand-row-{{ $items->id }}">
                                    <td colspan="{{ \Illuminate\Support\Facades\Auth::user()->hasRole(['Admin', 'Team-Leader', 'Branch-Manager']) ? 8 : 6 }}">
                                        <div class="expanded-inner-card">
                                            <div class="inner-project-header">
                                                <i class="mdi mdi-format-list-bulleted text-primary mr-2" style="font-size: 18px;"></i>
                                                Projects Portfolio for {{ $items->name }}
                                            </div>

                                            @if(count($items->projects) > 0)
                                            <table class="project-mini-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 5%">Sl</th>
                                                        <th style="width: 25%">Project Title</th>
                                                        <th style="width: 20%">Category</th>
                                                        <th style="width: 20%" class="text-center">Timeline</th>
                                                        <th style="width: 15%" class="text-center">Status</th>
                                                        <th style="width: 15%">Progress</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($items->projects as $pIdx => $project)
                                                    @php
                                                    $pStatus = strtolower($project->status);
                                                    $pBadge = 'badge-soft-secondary';
                                                    if($pStatus == 'active' || $pStatus == 'in progress') $pBadge = 'badge-soft-primary';
                                                    if($pStatus == 'completed') $pBadge = 'badge-soft-success';
                                                    if($pStatus == 'pending') $pBadge = 'badge-soft-warning';
                                                    if($pStatus == 'cancelled') $pBadge = 'badge-soft-danger';
                                                    @endphp
                                                    <tr>
                                                        <td class="font-weight-600 text-muted">{{ $pIdx + 1 }}</td>
                                                        <td>
                                                            <span class="font-weight-700 text-premium-dark">{{ $project->project_name }}</span>
                                                        </td>
                                                        <td>
                                                            <div class="font-weight-600">{{ $project->category->category ?? 'Service config' }}</div>
                                                            @if($project->sub_categories)
                                                            <div class="font-size-11 text-muted">{{ $project->sub_categories->name }}</div>
                                                            @endif
                                                        </td>
                                                        <td class="text-center font-size-12 text-muted">
                                                            <i class="mdi mdi-calendar"></i>
                                                            {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '---' }}
                                                            <span class="d-block text-muted-50">to {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '---' }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $pBadge }} px-2.5 py-1 badge-status-p">
                                                                {{ $project->status }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="progress-premium flex-grow-1">
                                                                    <div class="progress-premium-bar" style="width: {{ $project->progress }}%;"></div>
                                                                </div>
                                                                <span class="font-weight-700 text-primary font-size-12">{{ $project->progress }}%</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            @else
                                            <div class="text-center p-4" style="background-color: #ffffff; border: 1px dashed rgba(127, 0, 255, 0.15); border-radius: 12px;">
                                                <i class="mdi mdi-folder-open-outline text-muted mb-2" style="font-size: 32px; display: block;"></i>
                                                <span class="text-muted font-weight-500 font-size-13">No operational projects have been spawned for this matured client yet.</span>
                                            </div>
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <img src="{{ asset('assets/images/no-data.png') }}" alt="" style="max-height: 100px;" class="mb-3 d-block mx-auto">
                                        <h5 class="text-muted font-weight-600 font-size-16">No Matured Clients Registered</h5>
                                        <p class="text-muted-50 font-size-13">Matured conversions automatically propagate here once an executive successfully finishes a physical presentation pitch.</p>
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

                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

        // Expandable Projects Row slide mechanism
        $('.btn-trigger-expand').on('click', function() {
            let mainRow = $(this).closest('tr.client-main-row');
            let clientId = mainRow.data('client-id');
            let detailsRow = $('#expand-row-' + clientId);

            // Toggle active status on trigger button
            $(this).toggleClass('active');

            // Slide toggle or fade toggle details
            if (detailsRow.is(':visible')) {
                detailsRow.fadeOut(200);
            } else {
                detailsRow.fadeIn(250);
            }
        });
    });
</script>
@endsection
