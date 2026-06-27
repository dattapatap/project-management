@extends('layouts.app')

@section('content')
<div class="container-fluid erp-page pb-5">
    <div class="erp-page-header my-4">
        <div class="erp-page-header__main">
            <h4 class="erp-page-title">
                <i class="mdi mdi-ray-start-arrow mr-2 text-primary"></i>Sales Pipeline Kanban Board
            </h4>
            <p class="erp-page-subtitle">Track and manage active sales leads</p>
        </div>
        <div class="erp-page-header__actions">
            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm mr-2">
                <i class="mdi mdi-arrow-left mr-1"></i>Back to Home
            </a>
            <a href="{{ route('clients.index') }}" class="btn btn-primary btn-sm">
                <i class="mdi mdi-format-list-bulleted mr-1"></i>View Client List
            </a>
        </div>
    </div>

    <!-- Kanban Wrapper -->
    <div class="row">
        <div class="col-12">
            <div class="kanban-board-wrapper">
                <div class="kanban-scroller d-flex pb-4" style="overflow-x: auto; gap: 1.25rem; min-height: 70vh;">
                    
                    @foreach($pipeline as $stage => $clients)
                        @php
                            // Clean light palette card styling based on stage status
                            $colorMap = [
                                'Fresh'             => ['dot' => '#3b82f6', 'bg' => '#eff6ff', 'border' => '#bfdbfe', 'text' => '#1e40af', 'muted' => '#60a5fa'],
                                'Followup'          => ['dot' => '#06b6d4', 'bg' => '#ecfeff', 'border' => '#a5f3fc', 'text' => '#155e75', 'muted' => '#22d3ee'],
                                'Meeting Fixed'     => ['dot' => '#a855f7', 'bg' => '#faf5ff', 'border' => '#e9d5ff', 'text' => '#6b21a8', 'muted' => '#c084fc'],
                                'Hot Perspective'   => ['dot' => '#ef4444', 'bg' => '#fff1f2', 'border' => '#fecdd3', 'text' => '#9f1239', 'muted' => '#f87171'],
                                'Warm Perspective'  => ['dot' => '#f59e0b', 'bg' => '#fffbeb', 'border' => '#fde68a', 'text' => '#92400e', 'muted' => '#fbbf24'],
                                'Matured'           => ['dot' => '#10b981', 'bg' => '#ecfdf5', 'border' => '#a7f3d0', 'text' => '#065f46', 'muted' => '#34d399'],
                                'Not Interested'    => ['dot' => '#6b7280', 'bg' => '#f8fafc', 'border' => '#e2e8f0', 'text' => '#475569', 'muted' => '#94a3b8'],
                            ];
                            $theme = $colorMap[$stage] ?? $colorMap['Fresh'];
                        @endphp
                        
                        <div class="kanban-column d-flex flex-column rounded shadow-sm" 
                             style="min-width: 310px; width: 310px; background: #ffffff; border: 1px solid #e2e8f0; overflow: hidden;">
                            
                            <!-- Column Header - Pure White / Slate Background -->
                            <div class="kanban-column-header d-flex justify-content-between align-items-center px-3 py-3"
                                 style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <h6 class="mb-0 font-weight-bold d-flex align-items-center" style="color: #1e293b;">
                                    <span style="height: 10px; width: 10px; border-radius: 50%; background: {{ $theme['dot'] }};" class="d-inline-block mr-2"></span>
                                    {{ $stage }}
                                </h6>
                                <span class="badge badge-pill font-size-12 count-badge" style="background: #e2e8f0; color: #475569;">
                                    {{ $clients->count() }}
                                </span>
                            </div>

                            <!-- Column Body (List of Cards) -->
                            <div class="kanban-list flex-grow-1 px-2 pt-3" 
                                 id="kanban-list-{{ str_replace(' ', '-', strtolower($stage)) }}" 
                                 data-status="{{ $stage }}"
                                 style="min-height: 50vh; max-height: 65vh; overflow-y: auto; padding-bottom: 20px; background: #fafafa;">
                                
                                @foreach($clients as $client)
                                    <!-- Kanban Card - Colored based on stage -->
                                    <div class="card kanban-card mb-3 border shadow-sm" 
                                         data-id="{{ $client->id }}"
                                         style="background: {{ $theme['bg'] }}; border-color: {{ $theme['border'] }} !important; cursor: grab; transition: all 0.3s ease; border-left: 4px solid {{ $theme['dot'] }} !important; border-radius: 8px;">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0 font-size-14 font-weight-bold">
                                                    <a href="{{ route('client.detail', [$client->id, str_replace(' ', '-', strtolower($client->name))]) }}" style="color: {{ $theme['text'] }}; text-decoration: none;" class="pipeline-link">
                                                        {{ $client->name }}
                                                    </a>
                                                </h6>
                                                
                                                <!-- Dynamic Lead Score Badge -->
                                                @php
                                                    $leadScore = 0;
                                                    if ($client->status != 'Matured' && $client->status != 'Not Interested') {
                                                        $tbroDays = 0;
                                                        if ($client->history) {
                                                            $latest = $client->history;
                                                            if ($latest->tbro) {
                                                                $tbroDays = Carbon\Carbon::parse($latest->tbro)->diffInDays(now());
                                                            }
                                                        }
                                                        $leadScore = 100 - ($tbroDays * 2);
                                                        $leadScore = max(0, min(100, $leadScore));
                                                    }
                                                @endphp
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        $isOverdue = false;
                                                        if (in_array($stage, ['Followup', 'Meeting Fixed']) && $client->history && $client->history->tbro) {
                                                            $isOverdue = \Carbon\Carbon::parse($client->history->tbro)->lt(\Carbon\Carbon::today());
                                                        }
                                                    @endphp
                                                    @if($isOverdue)
                                                        <span class="badge badge-pill badge-danger font-size-10 shadow-sm mr-1 animate-pulse" title="Callback Overdue!">
                                                            <i class="mdi mdi-alert-circle"></i> Overdue
                                                        </span>
                                                    @endif
                                                    @if($leadScore > 0)
                                                        <span class="badge badge-pill font-size-11" style="background: rgba(251,191,36,0.2); color: #b45309; border: 1px solid rgba(251,191,36,0.3);" title="Lead Score">
                                                            <i class="mdi mdi-flash" style="font-size: 10px;"></i> {{ $leadScore }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <p class="font-size-13 mb-2" style="color: {{ $theme['text'] }}; opacity: 0.8;">
                                                <i class="mdi mdi-account-circle-outline mr-1"></i>{{ $client->cont_person }}
                                            </p>

                                            <!-- Last Remarks snippet -->
                                            @if($client->history)
                                                <div class="rounded p-2 mb-3 font-size-12" style="background: rgba(255,255,255,0.6); border: 1px solid {{ $theme['border'] }};">
                                                    <span class="font-weight-semibold" style="color: {{ $theme['text'] }}; opacity: 0.7; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Latest Remarks</span>
                                                    <span class="d-block text-truncate mt-1" style="color: #334155;" title="{{ $client->history->remarks }}">
                                                        {{ $client->history->remarks }}
                                                    </span>
                                                </div>
                                            @endif

                                            <div class="d-flex justify-content-between align-items-center font-size-12 pt-2" style="border-top: 1px solid {{ $theme['border'] }}; opacity: 0.9;">
                                                <span style="color: {{ $theme['text'] }}; opacity: 0.8;">
                                                    <i class="mdi mdi-clock-outline mr-1"></i>{{ $client->updated_at->diffForHumans() }}
                                                </span>
                                                <span class="font-weight-semibold badge px-2 py-1" style="background: rgba(255,255,255,0.7); color: {{ $theme['text'] }}; border: 1px solid {{ $theme['border'] }}; max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; border-radius: 6px;">
                                                    {{ $client->referral->name ?? 'Unassigned' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Follow-up / Action Modal -->
<div class="modal fade" id="scheduleFollowupModal" tabindex="-1" role="dialog" aria-labelledby="scheduleFollowupModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="scheduleFollowupModalLabel">
                    <i class="mdi mdi-calendar-clock mr-2 text-primary"></i>Schedule Callback / Action
                </h5>
                <button type="button" class="close text-dark cancel-modal-btn" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="scheduleFollowupForm">
                @csrf
                <input type="hidden" id="modal-client-id" name="client_id">
                <input type="hidden" id="modal-new-status" name="new_status">
                
                <div class="modal-body text-dark">
                    <div class="alert alert-info py-2 font-size-13">
                        You are moving this client to <strong id="modal-stage-name">-</strong>. Please schedule the next follow-up callback or action date.
                    </div>
                    <div class="form-group">
                        <label class="font-weight-semibold">Next Action Date <span class="text-danger">*</span></label>
                        <input type="date" id="modal-tbro" name="tbro" class="form-control border" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-semibold">Next Action Time</label>
                        <input type="time" id="modal-time" name="time" class="form-control border" value="10:00">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-semibold">Activity Remarks / Notes</label>
                        <textarea id="modal-remarks" name="remarks" class="form-control border" rows="3" placeholder="Enter details about what needs to be done next..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary cancel-modal-btn">Cancel</button>
                    <button type="submit" class="btn btn-primary shadow-sm">Save & Move</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .kanban-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08) !important;
    }
    .pipeline-link:hover {
        text-decoration: underline !important;
    }
    .kanban-scroller::-webkit-scrollbar {
        height: 8px;
    }
    .kanban-scroller::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .kanban-scroller::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .kanban-scroller::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .kanban-list::-webkit-scrollbar {
        width: 5px;
    }
    .kanban-list::-webkit-scrollbar-track {
        background: transparent;
    }
    .kanban-list::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }
    .sortable-ghost {
        opacity: 0.4;
        background: #e2e8f0 !important;
        border: 2px dashed #94a3b8 !important;
        border-radius: 8px !important;
    }
    .sortable-chosen {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-pulse {
        animation: pulse 1.8s infinite ease-in-out;
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    $(document).ready(function() {
        const columns = document.querySelectorAll('.kanban-list');
        let dragInfo = null;

        columns.forEach(col => {
            new Sortable(col, {
                group: 'sales-pipeline',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function (evt) {
                    const clientId = evt.item.getAttribute('data-id');
                    const newStatus = evt.to.getAttribute('data-status');
                    const oldStatus = evt.from.getAttribute('data-status');

                    if (newStatus === oldStatus) return;

                    // Keep drag details in memory in case we need to cancel
                    dragInfo = {
                        item: evt.item,
                        from: evt.from,
                        oldIndex: evt.oldIndex
                    };

                    // Open schedule modal
                    $('#modal-client-id').val(clientId);
                    $('#modal-new-status').val(newStatus);
                    $('#modal-stage-name').text(newStatus);
                    
                    // Reset modal inputs
                    $('#modal-tbro').val('');
                    $('#modal-remarks').val('');
                    
                    $('#scheduleFollowupModal').modal('show');
                }
            });
        });

        // Cancel button click -> Revert card DOM back to origin
        $('.cancel-modal-btn').on('click', function() {
            revertDrag();
            $('#scheduleFollowupModal').modal('hide');
        });

        function revertDrag() {
            if (dragInfo) {
                // Re-insert card at original index in the origin column
                const children = dragInfo.from.children;
                if (dragInfo.oldIndex >= children.length) {
                    dragInfo.from.appendChild(dragInfo.item);
                } else {
                    dragInfo.from.insertBefore(dragInfo.item, children[dragInfo.oldIndex]);
                }
                dragInfo = null;
            }
        }

        // Form Submit -> AJAX move card with schedule values
        $('#scheduleFollowupForm').on('submit', function(e) {
            e.preventDefault();
            
            const clientId = $('#modal-client-id').val();
            const newStatus = $('#modal-new-status').val();

            $.ajax({
                url: "{{ route('sales.pipeline.move') }}",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: $(this).serialize(),
                success: function(response) {
                    $('#scheduleFollowupModal').modal('hide');
                    if (response.success) {
                        alertify.success(response.message);
                        
                        // Reload the page to refresh stats, scores, and dates on UI cleanly
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        alertify.error(response.message || "Failed to update pipeline stage.");
                        revertDrag();
                    }
                },
                error: function(xhr) {
                    alertify.error("Error: Unauthorized or server error occurred.");
                    revertDrag();
                    $('#scheduleFollowupModal').modal('hide');
                }
            });
        });
    });
</script>
@endsection
