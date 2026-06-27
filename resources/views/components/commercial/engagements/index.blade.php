@extends('layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid erp-page">
    @include('layouts.partials.erp-page-header', [
        'title' => 'Commercial Engagements',
        'subtitle' => 'Tracked upsell / cross-sell orders — parent & child chain per client',
        'actions' => '<a href="' . url('/') . '" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-arrow-left mr-1"></i> Back</a>',
    ])
    <div class="card erp-table-card">
        <div class="card-body">
            <table id="engagementsTable" class="table table-bordered table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order No</th>
                        <th>Client</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Parent</th>
                        <th>Followed By</th>
                        <th>Converted By</th>
                        <th>Assigned At</th>
                        <th>Est. Value</th>
                        <th>Closed</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('components.commercial.engagements.partials.close-commercial-modal', ['categories' => $categories])
@endsection

@section('scripts')
<script src="{{ asset('js/csd-common.js') }}"></script>
<script>
$(function () {
    var $modal = $('#mdlCloseCommercial');

    function resetCloseCommercialForm() {
        var $form = $('#frmCloseCommercial');
        if ($form.length && $form[0]) {
            $form[0].reset();
        }
        $('#engagementSubCategory').empty().append('<option value="">Select Sub-Category</option>');
        $('#engOnlineWrap, #engChequeWrap').addClass('d-none');
        $('#engCashWrap').removeClass('d-none');
    }

    function closeCloseCommercialModal() {
        if (window.csdApi && typeof window.csdApi.closeModal === 'function') {
            window.csdApi.closeModal('#mdlCloseCommercial', '#frmCloseCommercial');
            return;
        }
        resetCloseCommercialForm();
        $modal.modal('hide');
        $modal.one('hidden.bs.modal', function () {
            $('body').removeClass('modal-open').css('padding-right', '');
            $('.modal-backdrop').remove();
        });
    }

    if ($modal.length && $modal.parent()[0] !== document.body) {
        $modal.appendTo('body');
    }

    var table = $('#engagementsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('commercial.engagements.data') }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false },
            { data: 'engagement_no' },
            { data: 'client_name' },
            { data: 'title' },
            { data: 'engagement_type' },
            { data: 'parent_no' },
            { data: 'csd_owner' },
            { data: 'sales_owner' },
            { data: 'assigned_at' },
            { data: 'estimated_value' },
            { data: 'closed_value' },
            { data: 'status' },
            { data: 'action', orderable: false },
        ],
    });

    $(document).on('click', '.closeCommercialBtn', function () {
        resetCloseCommercialForm();
        $('#closeEngagementId').val($(this).data('id'));
        $('#closeEngagementTitle').text($(this).data('title'));
        $modal.modal('show');
    });

    $('#engPaymentType').on('change', function () {
        $('#engOnlineWrap').toggleClass('d-none', this.value !== 'Online');
        $('#engChequeWrap').toggleClass('d-none', this.value !== 'Cheque');
        $('#engCashWrap').toggleClass('d-none', this.value !== 'Cash');
    });

    $('#engagementCategory').on('change', function () {
        var id = $(this).val();
        $('#engagementSubCategory').empty().append('<option value="">Select Sub-Category</option>');
        if (!id) return;
        $.get('{{ url('projectcategory/subcategories') }}', { projcategory: id }, function (res) {
            (res.data || []).forEach(function (r) {
                var label = r.text || r.name || r.category || '';
                $('#engagementSubCategory').append('<option value="' + r.id + '">' + label + '</option>');
            });
        });
    });

    $('#frmCloseCommercial').on('submit', function (e) {
        e.preventDefault();
        var id = $('#closeEngagementId').val();
        var formData = new FormData(this);
        formData.append('_token', '{{ csrf_token() }}');
        var $submit = $(this).find('[type="submit"]').prop('disabled', true);

        $.ajax({
            url: '{{ url('commercial/engagements') }}/' + id + '/close-commercial',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
        }).done(function (r) {
            if (r.success) {
                closeCloseCommercialModal();
                if (window.toastr) {
                    toastr.success(r.message);
                } else if (window.alertify) {
                    alertify.success(r.message);
                }
                table.ajax.reload(null, false);
            } else if (window.toastr) {
                toastr.error(r.message || 'Failed');
            } else if (window.alertify) {
                alertify.error(r.message || 'Failed');
            }
        }).fail(function (xhr) {
            var msg = xhr.responseJSON?.message || 'Validation failed';
            if (xhr.responseJSON?.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
            }
            if (window.toastr) {
                toastr.error(msg);
            } else if (window.alertify) {
                alertify.error(msg);
            }
        }).always(function () {
            $submit.prop('disabled', false);
        });
    });
});
</script>
@endsection
