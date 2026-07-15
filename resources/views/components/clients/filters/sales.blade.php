@extends('layouts.app')

@section('styles')
<style>
    :root {
        --clr-accent: #7F00FF;
        --clr-dark: #1e003c;
        --clr-mid: #3a007d;
        --grad-primary: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%);
        --grad-header: linear-gradient(135deg, #0e0021 0%, #1c003d 100%);
    }

    /* ── Page Header ── */
    .sales-page-header {
        background: var(--grad-header);
        border-radius: 14px;
        padding: 18px 24px;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 6px 22px rgba(14, 0, 33, 0.14);
    }

    .sales-page-header::after {
        content: '';
        position: absolute;
        top: -60%;
        right: -4%;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(225, 0, 255, 0.12) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .sales-page-header h4 {
        font-size: 16px !important;
        font-weight: 700 !important;
        margin-bottom: 2px !important;
        position: relative;
        z-index: 1;
    }

    .sales-page-header p {
        font-size: 11.5px !important;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    /* ── Main card ── */
    .sales-card {
        background: #fff;
        border: 1px solid rgba(127, 0, 255, 0.07);
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(31, 38, 135, 0.04);
        overflow: hidden;
    }

    /* ── Matured Clients Table ── */
    .matured-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }

    .matured-table thead th {
        background: #f3f0fa !important;
        color: #4c3c63 !important;
        font-weight: 700;
        font-size: 10.5px;
        text-transform: uppercase;
        letter-spacing: 0.55px;
        padding: 9px 12px !important;
        border-bottom: 2px solid rgba(127, 0, 255, 0.12) !important;
        border-top: none !important;
        white-space: nowrap;
    }

    .matured-table tbody tr.client-main-row {
        background: #fff;
        transition: background 0.15s ease;
        cursor: pointer;
    }

    .matured-table tbody tr.client-main-row:hover {
        background: #f9f7ff !important;
    }

    .matured-table tbody tr.client-main-row:nth-child(4n+3) {
        background: #fdfcff;
    }

    .matured-table tbody td {
        padding: 8px 12px !important;
        vertical-align: middle !important;
        color: #3d3355;
        font-size: 12.5px;
        border-top: none !important;
        border-bottom: 1px solid #ede9f8 !important;
        line-height: 1.4;
    }

    .client-name-main {
        font-weight: 700;
        font-size: 13px;
        color: #2d1f4a;
        display: block;
        line-height: 1.3;
    }

    .client-category-tag {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background: rgba(127, 0, 255, 0.07);
        color: #6b2fad;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 10px;
        margin-top: 3px;
    }

    /* ── Badges ── */
    .badge-matured-date {
        background: rgba(40, 167, 69, 0.08);
        color: #1a7c3e;
        font-weight: 600;
        font-size: 11px;
        padding: 3px 9px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        white-space: nowrap;
    }

    .badge-project-count {
        background: rgba(127, 0, 255, 0.07);
        color: var(--clr-accent);
        font-weight: 700;
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 20px;
        display: inline-block;
        border: 1px solid rgba(127, 0, 255, 0.12);
        white-space: nowrap;
    }

    /* ── Expand Toggle ── */
    .btn-toggle-expand {
        background: var(--clr-accent);
        color: #fff;
        border: none;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.22s ease;
        box-shadow: 0 3px 8px rgba(127, 0, 255, 0.2);
        cursor: pointer;
        flex-shrink: 0;
    }

    .btn-toggle-expand:hover {
        background: #6300c8;
        transform: scale(1.06);
    }

    .btn-toggle-expand.is-open {
        background: var(--clr-dark);
        transform: rotate(180deg);
        box-shadow: 0 3px 8px rgba(30, 0, 60, 0.2);
    }

    /* ── Expandable Project Panel ── */
    .expandable-details-row {
        display: none;
    }

    .expandable-details-row td {
        padding: 0 !important;
        border-bottom: 2px solid rgba(127, 0, 255, 0.1) !important;
    }

    .expanded-inner-card {
        padding: 14px 18px 16px;
        background: linear-gradient(180deg, #fdfbff 0%, #f7f5fa 100%);
        border-left: 3px solid var(--clr-accent);
    }

    .inner-project-header {
        font-size: 12px;
        font-weight: 700;
        color: #3b2258;
        letter-spacing: 0.3px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* ── Inner Mini Project Table ── */
    .project-mini-table {
        width: 100%;
        background: #fff;
        border-radius: 10px;
        border: 1px solid rgba(127, 0, 255, 0.06);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.015);
        font-size: 12px;
    }

    .project-mini-table th {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6c757d;
        background: #f9f8fc;
        padding: 7px 12px;
        border-bottom: 1px solid rgba(127, 0, 255, 0.07);
        letter-spacing: 0.4px;
        white-space: nowrap;
    }

    .project-mini-table td {
        padding: 7px 12px !important;
        font-size: 12px;
        border-bottom: 1px solid rgba(127, 0, 255, 0.04) !important;
        color: #3d3355;
    }

    .project-mini-table tr:last-child td {
        border-bottom: none !important;
    }

    /* ── Progress Bar ── */
    .progress-premium {
        height: 5px;
        border-radius: 10px;
        background: #e9e4f5;
        overflow: hidden;
    }

    .progress-premium-bar {
        height: 100%;
        border-radius: 10px;
        background: var(--grad-primary);
    }

    /* ── Empty state ── */
    .no-projects-empty {
        text-align: center;
        padding: 16px;
        background: #fff;
        border: 1px dashed rgba(127, 0, 255, 0.15);
        border-radius: 10px;
    }
</style>
@endsection

@section('content')

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="sales-page-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div>
            <h4 class="text-white">🏆 Matured Clients &amp; Project Progress Ledger</h4>
            <p class="text-white-50">Direct sales closures, ongoing configurations, and system-wide pipelines.</p>
        </div>
        <div class="mt-2 mt-md-0 d-flex align-items-center" style="position:relative;z-index:1;">
            <a href="{{ url('/') }}" class="btn btn-light btn-sm mr-3 font-weight-bold d-inline-flex align-items-center" style="border-radius: 8px; font-size:12px;">
                <i class="mdi mdi-arrow-left mr-1"></i> Back
            </a>
            <ol class="breadcrumb m-0 bg-transparent p-0" style="font-size:11.5px;">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white-50">{{ env('APP_NAME') }}</a></li>
                <li class="breadcrumb-item active text-white font-weight-600">Matured Clients</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="sales-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="matured-table  mb-0">
                            <thead>
                                <tr>
                                    <th style="width:4%">#</th>
                                    <th style="width:22%">Company</th>
                                    <th style="width:17%">Contact</th>
                                    @if(\Illuminate\Support\Facades\Auth::user()->hasRole(['Admin', 'Team-Leader', 'Branch-Manager']))
                                    <th style="width:12%">Created By</th>
                                    <th style="width:12%">Following By</th>
                                    @endif
                                    <th style="width:12%" class="text-center">Matured On</th>
                                    <th style="width:10%" class="text-center">Projects</th>
                                    <th style="width:11%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($clients as $key => $items)
                                {{-- Main client row — clicking expand button reveals projects --}}
                                <tr class="client-main-row" data-client-id="{{ $items->id }}">
                                    <td class="font-weight-600 text-muted text-center">
                                        {{ ($clients->currentpage()-1) * $clients->perpage() + $key + 1 }}
                                    </td>
                                    <td>
                                        <span class="client-name-main">{{ $items->name }}</span>
                                        @if($items->category)
                                        <span class="client-category-tag">
                                            <i class="mdi mdi-tag-outline"></i> {{ $items->category }}
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-weight-600" style="font-size:12.5px; color:#2d1f4a;">
                                            {{ $items->cont_person }}
                                            @if($items->designation)
                                            <span class="text-muted font-weight-400" style="font-size:11px;"> ({{ $items->designation }})</span>
                                            @endif
                                        </div>
                                        <div class="text-muted d-flex align-items-center mt-1" style="font-size:11px;">
                                            <i class="mdi mdi-phone-outline mr-1 text-primary"></i>{{ $items->mobile ?? '---' }}
                                        </div>
                                    </td>

                                    @if(\Illuminate\Support\Facades\Auth::user()->hasRole(['Admin', 'Team-Leader', 'Branch-Manager']))
                                    <td>
                                        <span class="font-weight-700 d-block" style="font-size:12.5px;color:#2d1f4a;">
                                            {{ $items->creator ? explode(' ', $items->creator->name)[0] : 'System' }}
                                        </span>
                                        <small class="text-muted" style="font-size:10.5px;">{{ $items->created_at ? $items->created_at->format('d M Y') : '---' }}</small>
                                    </td>
                                    <td>
                                        <span class="font-weight-700 d-block" style="font-size:12.5px;color:#2d1f4a;">
                                            {{ $items->referral ? explode(' ', $items->referral->name)[0] : '---' }}
                                        </span>
                                        <small class="text-muted" style="font-size:10.5px;">Assigned</small>
                                    </td>
                                    @endif

                                    <td class="text-center">
                                        <span class="badge-matured-date">
                                            <i class="mdi mdi-shield-check-outline"></i>
                                            {{ $items->active_from ? \Carbon\Carbon::parse($items->active_from)->format('d M Y') : 'N/A' }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge-project-count">
                                            <i class="mdi mdi-briefcase-outline mr-1"></i>{{ count($items->projects) }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center" style="gap:6px;">
                                            <button type="button" class="btn-toggle-expand btn-trigger-expand" title="View Projects">
                                                <i class="mdi mdi-chevron-down" style="font-size:16px;"></i>
                                            </button>
                                            <a class="btn btn-outline-primary btn-sm" target="_blank"
                                                href="{{ url('clients/'.base64_encode($items->id).'/sts') }}"
                                                title="Update Pipeline"
                                                style="border-radius:50%;width:28px;height:28px;padding:4px 0;font-size:13px;">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Expandable projects row --}}
                                <tr class="expandable-details-row" id="expand-row-{{ $items->id }}">
                                    <td colspan="{{ \Illuminate\Support\Facades\Auth::user()->hasRole(['Admin', 'Team-Leader', 'Branch-Manager']) ? 8 : 6 }}">
                                        <div class="expanded-inner-card">
                                            <div class="inner-project-header">
                                                <i class="mdi mdi-format-list-bulleted text-primary"></i>
                                                Projects Portfolio — <strong>{{ $items->name }}</strong>
                                            </div>

                                            @if(count($items->projects) > 0)
                                            <table class="project-mini-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width:4%">#</th>
                                                        <th style="width:26%">Project Title</th>
                                                        <th style="width:20%">Category</th>
                                                        <th style="width:20%" class="text-center">Timeline</th>
                                                        <th style="width:15%" class="text-center">Status</th>
                                                        <th style="width:15%">Progress</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($items->projects as $pIdx => $project)
                                                    @php
                                                    $pStatus = strtolower($project->status);
                                                    $pBadge = 'badge-soft-secondary';
                                                    if(in_array($pStatus, ['active','in progress'])) $pBadge = 'badge-soft-primary';
                                                    if($pStatus == 'completed') $pBadge = 'badge-soft-success';
                                                    if($pStatus == 'pending') $pBadge = 'badge-soft-warning';
                                                    if($pStatus == 'cancelled') $pBadge = 'badge-soft-danger';
                                                    @endphp
                                                    <tr>
                                                        <td class="text-muted font-weight-600">{{ $pIdx + 1 }}</td>
                                                        <td>
                                                            <span class="font-weight-700" style="color:#2d1f4a;">{{ $project->project_name }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-weight-600">{{ $project->projectCategory->category ?? 'Service config' }}</span>
                                                            @if($project->sub_categories)
                                                            <div class="text-muted" style="font-size:10.5px;">{{ $project->sub_categories->name }}</div>
                                                            @endif
                                                        </td>
                                                        <td class="text-center text-muted" style="font-size:11px;">
                                                            <i class="mdi mdi-calendar-range mr-1"></i>
                                                            {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '---' }}
                                                            <span class="d-block" style="font-size:10.5px;">to {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '---' }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $pBadge }}" style="font-size:10.5px;padding:3px 8px;border-radius:6px;">
                                                                {{ $project->status }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center" style="gap:6px;">
                                                                <div class="progress-premium flex-grow-1">
                                                                    <div class="progress-premium-bar" style="width:{{ $project->progress }}%;"></div>
                                                                </div>
                                                                <span class="font-weight-700 text-primary" style="font-size:11px;white-space:nowrap;">{{ $project->progress }}%</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            @else
                                            <div class="no-projects-empty">
                                                <i class="mdi mdi-folder-open-outline text-muted mb-1" style="font-size:28px;display:block;"></i>
                                                <span class="text-muted" style="font-size:12px;">No operational projects have been spawned for this client yet.</span>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="mdi mdi-briefcase-remove-outline text-muted mb-2" style="font-size:44px;display:block;"></i>
                                        <h6 class="text-muted font-weight-600" style="font-size:14px;">No Matured Clients Registered</h6>
                                        <p class="text-muted" style="font-size:12px;max-width:320px;margin:4px auto 0;">Matured conversions appear here once an executive successfully closes a client presentation.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(!$clients->isEmpty())
                    <div class="d-flex justify-content-end px-3 py-2" style="border-top:1px solid #ede9f8;">
                        {{ $clients->links("pagination::bootstrap-4") }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Expand/collapse project rows on button click
        $(document).on('click', '.btn-trigger-expand', function() {
            var $btn = $(this);
            var $mainRow = $btn.closest('tr.client-main-row');
            var clientId = $mainRow.data('client-id');
            var $detailRow = $('#expand-row-' + clientId);

            $btn.toggleClass('is-open');

            if ($detailRow.is(':visible')) {
                $detailRow.fadeOut(180);
            } else {
                $detailRow.fadeIn(220);
            }
        });
    });
</script>
@endsection
