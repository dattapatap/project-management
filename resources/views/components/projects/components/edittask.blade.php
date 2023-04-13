<div id="mdleditTask" class="modal fade" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Edit Task:</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_edit_task" class="custom-validation"  method="POST">
                    @csrf
                    <input type="hidden" value="" name="task_id" id="task_id">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label> Task Title <span class="text_required">*</span></label>
                                <input type="text" class="form-control" name="txt_task_title" id="txt_task_title" placeholder="Task Title" >
                                <span class="invalid-feedback" id="txt_task_title-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label> Priority <span class="text_required">*</span></label>
                            <div class="form-group">
                                <select  class="form-control" name="txt_task_priority" id="txt_task_priority" placeholder="Status">
                                    <option value="Low" > Low </option>
                                    <option value="Medium" > Medium </option>
                                    <option value="High" > High </option>
                                </select>
                                <span class="invalid-feedback" id="txt_task_priority-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label> Est. Start Date <span class="text_required">*</span></label>
                            <div class="form-group">
                                <input type="text" class="form-control" name="txt_task_est_start_date" id="txt_task_est_start_date"
                                placeholder="Est Start Date" min="<?= date('Y-m-d'); ?>" >
                                <span class="invalid-feedback" id="txt_task_est_start_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label> Est. End Date <span class="text_required">*</span></label>
                            <div class="form-group">
                                <input type="datepicker" class="form-control" name="txt_task_est_end_date" id="txt_task_est_end_date"
                                placeholder="Est. End Date"  min="<?= date('Y-m-d'); ?>">
                                <span class="invalid-feedback" id="txt_task_est_end_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="form-group">
                                <label> Assign To <span class="text_required">*</span></label>
                                <select  class="form-control select2" name="txt_task_user" id="txt_task_user" >
                                    <option value="" selected>Choose Employee</option>
                                    <option value="{{ $user->id }}">Assign To Me</option>
                                    @php
                                        $employees = DB::table('team_members')->select('user')->where('department', 2)
                                                            ->where('deleted_at', null)->get();
                                    @endphp
                                    @foreach ($employees as $item )
                                        @php
                                            $emp = DB::table('users')->select('id', 'name')->where('id', $item->user )->first();
                                        @endphp
                                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                    @endforeach

                                </select>
                                <span class="invalid-feedback" id="txt_task_user-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-12 float-right">
                            <label> Task Description <span class="text_required">*</span></label>
                            <textarea class="form-control" name="txt_task_description" id="txt_task_description"></textarea>
                            <span class="invalid-feedback" id="txt_task_description-input-error" role="alert"> <strong></strong></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"> Update </button>
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

        let tinytask = tinymce.init({
                selector: 'textarea#txt_task_description',
                branding: false,
                table_grid: false,
                plugins: [
                    "advlist autolink link image lists charmap print preview hr anchor pagebreak",
                    "searchreplace visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                    "save table contextmenu directionality emoticons paste textcolor"
                ],
                toolbar: "undo redo | fontselect styleselect fontsizeselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | codesample action section button",
                font_formats:"Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Book Antiqua=book antiqua,palatino; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino;",
                fontsize_formats: "8px 9px 10px 11px 12px 14px 16px 18px 20px 22px 24px 26px 28px 30px 32px",
                height: 300,
        });

        $('#txt_task_est_start_date, #txt_task_est_end_date').datetimepicker({
            minDate: moment().subtract(1,'d'),
            allowInputToggle: false,
            // useCurrent: true,
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

        $('.edittask').click(function(eve){
            let taskid = $(this).attr('taskid')
            eve.preventDefault();
            $.ajax({
                type: 'GET',
                url: base_url +'/projects/taskboard/'+ taskid +'/edit',
                dataType:'json',
                beforeSend: function() {
                    $('#cover-spin').css('display', 'block');
                },
                success: function(response) {
                    $('#cover-spin').css('display', 'none');
                    if(response.success == true){
                        const task = response.task
                        $('#task_id').val(task.id)
                        $('#txt_task_title').val(task.title)
                        $('#txt_task_priority').val(task.priority)
                        $('#txt_task_est_start_date').val(moment(task.startdate).format('DD/MM/YYYY hh:mm A'))
                        $('#txt_task_est_end_date').val(moment(task.enddate).format('DD/MM/YYYY hh:mm A'))
                        $('#txt_task_user').val(task.assigned_to).trigger('change')

                        tinymce.get("txt_task_description").setContent(task.description);
                        $('#mdleditTask').modal('show');
                    }else{
                        alertify.error(response.message);
                    }
                },
                error: function(response) {
                    $('#cover-spin').css('display', 'none');
                }
            });
        });

        $('#frm_edit_task').on('submit', function(eve){
            eve.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");
            $.ajax({
                type: 'POST',
                url: base_url +'/projects/taskboard/'+ $('#task_id').val() +'/update',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#cover-spin').css('display', 'block');
                },
                success: function(response) {
                    $('#cover-spin').css('display', 'none');
                    if(response.success == true){
                        alertify.success(response.message);
                        $('#mdleditTask').modal('hide');
                        $('#mdleditTask')[0].reset();
                        window.location.reload()
                    }else{
                        alertify.error(response.message);
                    }
                },
                error: function(response) {
                    $('#cover-spin').css('display', 'none');
                    if (response.status === 422) {
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
