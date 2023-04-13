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
                <form id="frm_task_status" class="custom-validation"  method="POST">
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
                            <input type="text" name="act_start_date" id="act_start_date" class="form-control">
                            <span class="invalid-feedback" id="act_start_date-input-error" role="alert"> <strong></strong></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"
                                > Update</button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>

    $(document).ready(function(){
        $(document).on('click', '.changeStatus', function(eve){
            $('#taskid').val($(this).attr('taskid'))
            $('#mdlChangeStatus').modal('show');
        });

        $('#status').change(function(){

            if($(this).val() == 'InProgress'){
                $('.taskdate').css('display', 'block');
            }else{
                $('.taskdate').css('display', 'none');
            }
        })

        $('#act_start_date').datetimepicker({
            minDate: moment().subtract(1,'d'),
            allowInputToggle: false,
            locale: moment().local('en'),
            format: 'DD/MM/YYYY hh:mm A',
            icons: {
                time: 'mdi mdi-clock-outline',
                date: 'fa fa-calendar',
                up: 'fa fa-chevron-up',
                down: 'fa fa-chevron-down',
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-check',
                clear: 'fa fa-trash',
                close: 'mdi mdi-clock-outline'
            }
        })

        $('#frm_task_status').on('submit', function(ev){
            ev.preventDefault();
            $(".invalid-feedback").children("strong").text("");
            $.ajax({
                type: 'POST',
                url: base_url +'/projects/taskboard/changestatus',
                data: { 'status': $('#status').val(), 'taskid':$('#taskid').val(), 'act_start_date':$('#act_start_date').val() },
                beforeSend: function() {
                    $('#cover-spin').css('display', 'block');
                },
                success: function(response) {
                    $('#cover-spin').css('display', 'none');
                    if(response.success == true){
                        alertify.success(response.message);
                        $('#mdlChangeStatus').modal('hide');
                        $('#frm_task_status')[0].reset();
                        location.reload();
                    }else{
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
