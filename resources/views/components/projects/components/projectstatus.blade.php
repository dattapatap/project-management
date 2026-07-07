<div id="mdlProjectStatus" class="modal fade" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Project Status:</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="font-size: 2rem; line-height: 1;">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_project_status" class="custom-validation" method="POST">
                    @csrf
                    <input type="hidden" value="" name="projectstatusid" id="projectstatusid">
                    <div class="row">
                        <div class="col-md-12 ">
                            <label>Project Status <span class="text_required">*</span></label>
                            <select class="form-control" id="project_status_select" name="status" style="width: 100%">
                                <option value="">Select Status</option>
                                <option value="ToDo">ToDo</option>
                                <option value="InProgress">InProgress</option>
                                <option value="Completed">Completed</option>
                            </select>
                            <span class="invalid-feedback" id="status-input-error" role="alert"> <strong></strong></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"> Update</button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $(document).on('click', '.btn_project_status', function(eve) {
            let projectId = $(this).attr('projectid');
            let currentStatus = $(this).attr('status');

            $('#projectstatusid').val(projectId);

            // Store current status in data attribute
            $('#project_status_select').data('current-status', currentStatus);

            // Rebuild the select options dynamically based on currentStatus and role
            let statusSelect = $('#project_status_select');
            statusSelect.empty().append('<option value="">Select Status</option>');

            let isManagement = {{ Auth::user()->hasRole(['Admin', 'Branch-Manager', 'Team-Leader']) ? 'true' : 'false' }};

            if (currentStatus === 'ToDo' || !currentStatus) {
                statusSelect.append('<option value="ToDo">ToDo</option>');
                statusSelect.append('<option value="InProgress">InProgress</option>');
            } else if (currentStatus === 'InProgress') {
                statusSelect.append('<option value="InProgress">InProgress</option>');
                statusSelect.append('<option value="Completed">Completed</option>');
            } else if (currentStatus === 'Completed') {
                if (isManagement) {
                    statusSelect.append('<option value="InProgress">InProgress</option>');
                }
                statusSelect.append('<option value="Completed">Completed</option>');
            }

            statusSelect.val(currentStatus).change();
            $('#mdlProjectStatus').modal('show');
        });

        $('#project_status_select').change(function() {
            $('.taskdate').css('display', 'none');
        })

        $('#frm_project_status').on('submit', function(ev) {
            ev.preventDefault();

            let selectedStatus = $('#project_status_select').val();
            let currentStatus = $('#project_status_select').data('current-status') || '';

            if (selectedStatus !== currentStatus && (selectedStatus === 'InProgress' || selectedStatus === 'Completed')) {
                let title = 'Change Status';
                let msg = 'Are you sure you want to change project status to ' + selectedStatus + '? You will not be able to revert it.';
                if (selectedStatus === 'Completed') {
                    title = 'Complete Project';
                    msg = 'Are you sure you want to complete this project? You will not be able to revert it.';
                }
                alertify.confirm(title, msg, 
                    function() {
                        submitProjectStatusForm();
                    }, 
                    function() {
                        // User cancelled
                    }
                );
            } else {
                submitProjectStatusForm();
            }
        });

        function submitProjectStatusForm() {
            $(".invalid-feedback").children("strong").text("");
            $.ajax({
                type: 'POST',
                url: base_url + '/projects/changestatus',
                data: {
                    'status': $('#project_status_select').val(),
                    'projectid': $('#projectstatusid').val()
                },
                beforeSend: function() {
                    $('#cover-spin').css('display', 'block');
                },
                success: function(response) {
                    $('#cover-spin').css('display', 'none');
                    if (response.success == true) {
                        alertify.success(response.message);
                        $('#mdlProjectStatus').modal('hide');
                        $('#frm_project_status')[0].reset();
                        location.reload();
                    } else {
                        alertify.error(response.message);
                    }
                },
                error: function(response) {
                    $('#cover-spin').css('display', 'none');
                    if (response.responseJSON && response.responseJSON.status === 400) {
                        let errors = response.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            $("#" + key + "Input").addClass("is-invalid");
                            $("#" + key + "-input-error").children("strong").text(errors[key][0]);
                        });
                    }
                }
            });
        }

    })
</script>
