<div id="mdlTaslLog" class="modal fade" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Task Logs:</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_task_log" class="custom-validation" method="POST">
                    @csrf
                    <input type="hidden" value="" name="tasklog" id="tasklog">
                    <div class="row">

                        <div class="col-md-4 mt-2">
                            <label>Log Date<span class="text_required">*</span></label>
                            <div class="input-group">
                                <input type="date" name="log_date" id="log_date" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                            </div>
                            <span class="invalid-feedback" id="log_date-input-error" role="alert"> <strong></strong></span>
                        </div>
                        <div class="col-md-3 mt-2">
                            <label>Start Time<span class="text_required">*</span></label>
                            <div class="input-group">
                                <input type="time" name="log_start_time" id="log_start_time" class="form-control">
                            </div>
                            <span class="invalid-feedback" id="log_start_time-input-error" role="alert"> <strong></strong></span>
                        </div>
                        <div class="col-md-3 mt-2">
                            <label>End Time<span class="text_required">*</span></label>
                            <div class="input-group">
                                <input type="time" name="log_end_time" id="log_end_time" class="form-control">
                            </div>
                            <span class="invalid-feedback" id="log_end_time-input-error" role="alert"> <strong></strong></span>
                        </div>
                        <div class="col-md-2 mt-2">
                            <label>Time Spend<span class="text_required">*</span></label>
                            <input type="text" name="log_time_spend" id="log_time_spend" class="form-control" readonly>
                            <span class="invalid-feedback" id="log_time_spend-input-error" role="alert"> <strong></strong></span>
                        </div>

                        <div class="col-md-12 mt-2">
                            <label>Log Description( Work Descriptions )<span class="text_required">*</span></label>
                            <textarea name="log_description" id="log_description" class="form-control"></textarea>
                            <span class="invalid-feedback" id="log_description-input-error" role="alert"> <strong></strong></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"> Add </button>
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
        $(document).on('click', '.tasklog', function(eve) {
            $('#tasklog').val($(this).attr('taskid'))
            $('#mdlTaslLog').modal('show');
        });

        function calculateTime() {
            var start = $('#log_start_time').val();
            var end = $('#log_end_time').val();

            if (start && end) {
                var startParts = start.split(':');
                var endParts = end.split(':');

                var startDate = new Date(0, 0, 0, startParts[0], startParts[1], 0);
                var endDate = new Date(0, 0, 0, endParts[0], endParts[1], 0);

                if (endDate < startDate) {
                    endDate.setDate(endDate.getDate() + 1);
                }

                var diff = endDate - startDate;
                var hours = (diff / 1000 / 3600).toFixed(2);

                $('#log_time_spend').val(hours);
            }
        }

        $('#log_start_time, #log_end_time').on('change', function() {
            calculateTime();
        });

        $('#frm_task_log').on('submit', function(eve) {
            eve.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");

            $.ajax({
                type: 'POST',
                url: base_url + '/projects/taskboard/addLog',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#cover-spin').css('display', 'block');
                },
                success: function(response) {
                    $('#cover-spin').css('display', 'none');
                    console.log(response);
                    if (response.success == true) {
                        alertify.success(response.message);
                        $('#mdlTaslLog').modal('hide');
                        $('#frm_task_log')[0].reset();
                    } else {
                        alertify.error(response.message);
                    }
                },
                error: function(response) {
                    console.log(response);
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


    function calculateLogTime() {
        let start = $('#log_start_time').val();
        let end = $('#log_end_time').val();
        if (start != '' && end != '') {
            let timestart = moment(start, 'hh:mm A');
            let timeend = moment(end, 'hh:mm A');

            let duration = moment.duration(timeend.diff(timestart));
            let hours = Math.abs(duration.asHours()).toFixed(2);
            $('#log_time_spend').val(hours);
        }
    }
</script>
