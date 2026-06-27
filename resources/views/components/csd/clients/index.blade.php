@extends('layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
<link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid erp-page erp-page--csd">
    @php
        $backBtn = '<a href="' . url('/') . '" class="btn btn-outline-primary btn-sm mr-2"><i class="mdi mdi-arrow-left mr-1"></i> Back</a>';
        $headerActions = $backBtn . ($canAssign
            ? '<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mdlAddAssignment"><i class="mdi mdi-plus"></i> Assign Client</button>'
            : '');
    @endphp
    @include('layouts.partials.erp-page-header', [
        'title' => 'CSD Client Management',
        'subtitle' => 'Track health, satisfaction, and ownership of your portfolio.',
        'actions' => $headerActions,
    ])
    <div class="card erp-table-card">
        <div class="card-body">
            <table id="csdClientsTable" class="table table-bordered table-hover dt-responsive nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Project</th>
                        <th>Assigned To</th>
                        <th>Handoff</th>
                        <th>Health</th>
                        <th>Satisfaction</th>
                        <th>Open Upsell Order</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@if($canAssign)
@include('components.csd.partials.assignment-modal', ['executives' => $executives])
@endif

<div id="mdlEditAssignment" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content csd-modal__content">
            <form id="frmEditAssignment">
                @csrf
                <input type="hidden" id="editAssignmentId">
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Update Assignment</p>
                        <h5 class="modal-title csd-modal__title" id="editClientName">Update Assignment</h5>
                    </div>
                    <button type="button" class="close csd-modal__close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body csd-modal__body">
                    <div class="row">
                        @if($canAssign)
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Assign To</label>
                                <select name="assigned_to" id="editAssignedTo" class="form-control">
                                    <option value="">Unassigned</option>
                                    @foreach($executives as $exec)
                                    <option value="{{ $exec->id }}">{{ $exec->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Health Status <span class="text-danger">*</span></label>
                                <select name="health_status" id="editHealthStatus" class="form-control" required>
                                    <option value="healthy">Healthy</option>
                                    <option value="at_risk">At Risk</option>
                                    <option value="churning">Churning</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" id="editStatus" class="form-control" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Satisfaction (1–10)</label>
                                <input type="number" name="satisfaction_score" id="editSatisfaction" class="form-control" min="1" max="10">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group csd-field mb-0">
                                <label>Notes</label>
                                <textarea name="notes" id="editNotes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save-outline mr-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{ asset('js/csd-common.js') }}"></script>
<script>
$(function() {
    var table = $('#csdClientsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('csd.clients.data') }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'client_name', name: 'client_name' },
            { data: 'project_name', orderable: false },
            { data: 'assignee_name', orderable: false },
            { data: 'handoff_date' },
            { data: 'health_status', orderable: false },
            { data: 'satisfaction_score' },
            { data: 'upsell_track', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    @if($canAssign)
    $.get('{{ route('csd.clients.active') }}', function(res) {
        if (res.success && res.data.length) {
            $('#csdClientSelect').html('<option value="">Select client…</option>' + res.data.map(function(c) {
                return '<option value="' + c.id + '">' + c.name + '</option>';
            }).join(''));
        } else {
            $('#csdClientSelect').html('<option value="">No eligible clients</option>');
        }
    });
    $('#frmCsdAssignment').on('submit', function(e) {
        e.preventDefault();
        csdApi.post('{{ route('csd.clients.store') }}', $(this).serialize(), function() {
            csdApi.closeModal('#mdlAddAssignment', '#frmCsdAssignment');
            table.ajax.reload();
        });
    });
    @endif

    $(document).on('click', '.editAssignment', function() {
        var id = $(this).data('id');
        csdApi.get('/csd/clients/' + id, function(res) {
            if (!res.success) return;
            var a = res.assignment;
            $('#editAssignmentId').val(a.id);
            $('#editClientName').text(a.client ? a.client.name : 'Update Assignment');
            if ($('#editAssignedTo').length) { $('#editAssignedTo').val(a.assigned_to || ''); }
            $('#editHealthStatus').val(a.health_status);
            $('#editStatus').val(a.status);
            $('#editSatisfaction').val(a.satisfaction_score || '');
            $('#editNotes').val(a.notes || '');
            $('#mdlEditAssignment').modal('show');
        });
    });

    $('#frmEditAssignment').on('submit', function(e) {
        e.preventDefault();
        var id = $('#editAssignmentId').val();
        csdApi.put('/csd/clients/' + id, $(this).serializeArray().reduce(function(o, i) {
            o[i.name] = i.value;
            return o;
        }, {}), function() {
            csdApi.closeModal('#mdlEditAssignment', '#frmEditAssignment');
            table.ajax.reload();
        });
    });
});
</script>
@endsection
