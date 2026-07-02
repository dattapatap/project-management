<div id="mdlChangeStatus" class="modal fade" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Task Status:</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_task_status" class="custom-validation" method="POST">
                    @csrf
                    <input type="hidden" value="" name="taskid" id="taskid">
                    <div class="row">
                        <div class="col-md-12 ">
                            <label>Task Status <span class="text_required">*</span></label>
                            <select class="form-control" id="status" name="status" style="width: 100%">
                                <option value="">Select Status</option>
                                <option value="ToDo">ToDo</option>
                                <option value="InProgress">InProgress</option>
                                <option value="Completed">Completed</option>
                            </select>
                            <span class="invalid-feedback" id="status-input-error" role="alert"> <strong></strong></span>
                        </div>
                        <div class="col-md-12 mt-2 taskdate" style="display: none;">
                            <label>Actual Task Start Date <span class="text_required">*</span></label>
                            <input type="datetime-local" name="act_start_date" id="act_start_date" class="form-control" value="{{ date('Y-m-d\TH:i') }}">
                            <span class="invalid-feedback" id="act_start_date-input-error" role="alert"> <strong></strong></span>
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
        $(document).on('click', '.changeStatus', function(eve) {
            let taskId = $(this).attr('taskid');
            let currentStatus = $(this).attr('currentstatus');

            $('#taskid').val(taskId);

            // Store current status in data attribute
            $('#status').data('current-status', currentStatus);

            // Rebuild the select options dynamically based on currentStatus and role
            let statusSelect = $('#status');
            statusSelect.empty().append('<option value="">Select Status</option>');

            let isManagement = {
                {
                    Auth::user() - > hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader']) ? 'true' : 'false'
                }
            };

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
            $('#mdlChangeStatus').modal('show');
        });

        $('#status').change(function() {
            if ($(this).val() == 'InProgress') {
                $('.taskdate').css('display', 'block');
            } else {
                $('.taskdate').css('display', 'none');
            }
        })

        $('#frm_task_status').on('submit', function(ev) {
            ev.preventDefault();

            let selectedStatus = $('#status').val();
            let currentStatus = $('#status').data('current-status') || '';

            if (selectedStatus !== currentStatus && (selectedStatus === 'InProgress' || selectedStatus === 'Completed')) {
                let msg = 'Are you sure you want to change status to ' + selectedStatus + '? You will not be able to revert it back to ToDo.';
                if (selectedStatus === 'Completed') {
                    msg = 'Are you sure you want to complete this task? You will not be able to revert it back to ToDo.';
                }
                if (!confirm(msg)) {
                    return false;
                }
            }

            $(".invalid-feedback").children("strong").text("");
            $.ajax({
                type: 'POST',
                url: base_url + '/projects/taskboard/changestatus',
                data: {
                    'status': $('#status').val(),
                    'taskid': $('#taskid').val(),
                    'act_start_date': $('#act_start_date').val()
                },
                beforeSend: function() {
                    $('#cover-spin').css('display', 'block');
                },
                success: function(response) {
                    $('#cover-spin').css('display', 'none');
                    if (response.success == true) {
                        alertify.success(response.message);
                        $('#mdlChangeStatus').modal('hide');
                        $('#frm_task_status')[0].reset();
                        location.reload();
                    } else {
                        alertify.error(response.message);
                    }
                },
                error: function(response) {
                    $('#cover-spin').css('display', 'none');
                    if (response.responseJSON.status === 400) {
                        let errors = response.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            $("#" + key + "Input").addClass("is-invalid");
                            $("#" + key + "-input-error").children("strong").text(errors[key][0]);
                        });
                    }
                }
            });

        })

    })
</script>
