@extends('layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid erp-page erp-page--csd">
    @include('layouts.partials.erp-page-header', [
        'title' => 'Communication Center',
        'actions' => '<a href="' . url('/') . '" class="btn btn-outline-primary btn-sm mr-2"><i class="mdi mdi-arrow-left mr-1"></i> Back</a><button type="button" class="btn btn-primary btn-sm" id="btnLogCommunication"><i class="mdi mdi-plus"></i> Log Communication</button>',
    ])
    <div class="card erp-table-card">
        <div class="card-body">
            <table id="dataTable" class="table table-bordered table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th><th>Client</th><th>Type</th><th>Subject</th>
                        <th>Date</th><th>Next Follow-up</th><th>By</th><th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<div id="mdlAdd" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content csd-modal__content">
            <form id="frmAdd">
                @csrf
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Communications</p>
                        <h5 class="modal-title csd-modal__title">Log Communication</h5>
                    </div>
                    <button type="button" class="close csd-modal__close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body csd-modal__body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Client <span class="text-danger">*</span></label>
                                <select name="client" class="form-control" required>
                                    <option value="">Select client…</option>
                                    @foreach($clients as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Type</label>
                                <select name="type" class="form-control">
                                    <option value="call">Call</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="email">Email</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="note">Note</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group csd-field">
                                <label>Subject</label>
                                <input type="text" name="subject" class="form-control" placeholder="Optional subject">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group csd-field">
                                <label>Remarks <span class="text-danger">*</span></label>
                                <textarea name="remarks" class="form-control" rows="3" required placeholder="What was discussed…"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Communication Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="communication_date" class="form-control" required value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Next Follow-up</label>
                                <input type="date" name="next_followup" class="form-control">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group csd-field mb-0">
                                <label>Minutes of Meeting (MOM)</label>
                                <textarea name="mom" class="form-control" rows="3" placeholder="Optional meeting notes…"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Log</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="mdlView" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content csd-modal__content">
            <div class="modal-header csd-modal__header">
                <div>
                    <p class="csd-modal__eyebrow">CSD · Communications</p>
                    <h5 class="modal-title csd-modal__title">Communication Details</h5>
                </div>
                <button type="button" class="close csd-modal__close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body csd-modal__body" id="viewCommBody"></div>
            <div class="modal-footer csd-modal__footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div id="mdlEditComm" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content csd-modal__content">
            <form id="frmEditComm">
                @csrf
                <input type="hidden" id="editCommId">
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Communications</p>
                        <h5 class="modal-title csd-modal__title">Edit Communication</h5>
                    </div>
                    <button type="button" class="close csd-modal__close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body csd-modal__body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Type</label>
                                <select name="type" id="editCommType" class="form-control">
                                    <option value="call">Call</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="email">Email</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="note">Note</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Subject</label>
                                <input type="text" name="subject" id="editCommSubject" class="form-control">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group csd-field">
                                <label>Remarks <span class="text-danger">*</span></label>
                                <textarea name="remarks" id="editCommRemarks" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Communication Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="communication_date" id="editCommDate" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Next Follow-up</label>
                                <input type="date" name="next_followup" id="editCommFollowup" class="form-control">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group csd-field mb-0">
                                <label>MOM</label>
                                <textarea name="mom" id="editCommMom" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('js/csd-common.js') }}"></script>
<script>
$(function(){
    csdApi.mountModals();

    var defaultCommDate = @json(now()->format('Y-m-d\TH:i'));

    var table = $('#dataTable').DataTable({processing:true,serverSide:true,ajax:'{{ route('csd.communications.data') }}',
        columns:[{data:'DT_RowIndex',orderable:false},{data:'client_name'},{data:'type'},{data:'subject'},{data:'communication_date'},{data:'next_followup'},{data:'creator_name'},{data:'action',orderable:false}]});

    $('#btnLogCommunication').on('click', function () {
        $('#frmAdd')[0]?.reset();
        $('input[name="communication_date"]', '#frmAdd').val(defaultCommDate);
        $('#mdlAdd').modal('show');
    });

    $('#frmAdd').on('submit', function (e) {
        e.preventDefault();
        csdApi.post('{{ route('csd.communications.store') }}', $(this).serialize(), function () {
            csdApi.closeModal('#mdlAdd', '#frmAdd');
            $('input[name="communication_date"]', '#frmAdd').val(defaultCommDate);
            table.ajax.reload(null, false);
        });
    });
    $(document).on('click','.viewComm',function(){
        csdApi.get('/csd/communications/'+$(this).data('id'),function(r){
            if(!r.success)return; var d=r.data;
            $('#viewCommBody').html(`<p><strong>Client:</strong> ${d.client?.name||'-'}<br><strong>Type:</strong> ${d.type}<br><strong>Subject:</strong> ${d.subject||'-'}<br><strong>Remarks:</strong> ${d.remarks}<br><strong>MOM:</strong> ${d.mom||'-'}</p>`);
            $('#mdlView').modal('show');
        });
    });
    $(document).on('click','.editComm',function(){
        csdApi.get('/csd/communications/'+$(this).data('id'),function(r){
            if(!r.success)return; var d=r.data;
            $('#editCommId').val(d.id);
            $('#editCommType').val(d.type);
            $('#editCommSubject').val(d.subject);
            $('#editCommRemarks').val(d.remarks);
            $('#editCommMom').val(d.mom);
            $('#editCommDate').val(d.communication_date ? d.communication_date.replace(' ','T').substring(0,16) : '');
            $('#editCommFollowup').val(d.next_followup ? d.next_followup.substring(0,10) : '');
            $('#mdlEditComm').modal('show');
        });
    });
    $('#frmEditComm').on('submit',function(e){e.preventDefault();var id=$('#editCommId').val();
        csdApi.put('/csd/communications/'+id,$(this).serializeArray().reduce(function(o,i){o[i.name]=i.value;return o;},{}),function(){
            csdApi.closeModal('#mdlEditComm','#frmEditComm');
            table.ajax.reload(null, false);
        });});
});
</script>
@endsection
