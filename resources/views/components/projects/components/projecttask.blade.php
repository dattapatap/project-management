<div id="mdlTask" class="modal fade" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Add Task:</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_task" class="custom-validation"  method="POST">
                    @csrf
                    <input type="hidden" value="" name="task_projectid" id="task_projectid">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label> Task Title <span class="text_required">*</span></label>
                                <input type="text" class="form-control" name="task_title" id="task_title" placeholder="Task Title" tabindex="1">
                                <span class="invalid-feedback" id="task_title-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label> Priority <span class="text_required">*</span></label>
                            <div class="form-group">
                                <select  class="form-control" name="task_priority" id="task_priority" placeholder="Status" tabindex="2">
                                    <option value="Low" > Low </option>
                                    <option value="Medium" > Medium </option>
                                    <option value="High" > High </option>
                                </select>
                                <span class="invalid-feedback" id="task_priority-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label> Est. Start Date <span class="text_required">*</span></label>
                            <div class="form-group">
                                <input type="date" class="form-control" name="task_est_start_date" id="task_est_start_date"
                                placeholder="Est Start Date" tabindex="3" min="<?= date('Y-m-d'); ?>" >
                                <span class="invalid-feedback" id="task_est_start_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label> Est. End Date <span class="text_required">*</span></label>
                            <div class="form-group">
                                <input type="date" class="form-control" name="task_est_end_date" id="task_est_end_date"
                                placeholder="Est. End Date" tabindex="4" min="<?= date('Y-m-d'); ?>">
                                <span class="invalid-feedback" id="task_est_end_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="form-group">
                                <label> Assign To <span class="text_required">*</span></label>
                                <select  class="form-control select2" name="task_user" id="task_user" tabindex="2" tabindex="5">
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
                                <span class="invalid-feedback" id="task_user-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-12 float-right">
                            <label> Task Description <span class="text_required">*</span></label>
                            <textarea class="form-control" name="task_description" id="task_description" tabindex="6"></textarea>
                            <span class="invalid-feedback" id="task_description-input-error" role="alert"> <strong></strong></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"
                                tabindex="6"> Add </button>
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

        tinymce.init({
                selector: 'textarea#task_description',
                branding: false,
                table_grid: false,
                plugins: [
                    "advlist autolink lists link image charmap print preview anchor",
                    "searchreplace visualblocks code fullscreen",
                    "insertdatetime media paste table codesample"
                ],
                toolbar: "undo redo | fontselect styleselect fontsizeselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | codesample action section button",
                font_formats:"Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Book Antiqua=book antiqua,palatino; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino;",
                fontsize_formats: "8px 9px 10px 11px 12px 14px 16px 18px 20px 22px 24px 26px 28px 30px 32px 34px 36px 38px 40px 42px 44px 46px 48px 50px 52px 54px",
                height: 300
        });

        $(document).on('click', '.btn_add_task', function(eve){
            $('#task_projectid').val($(this).attr('projectid'))
            $('#mdlTask').modal('show');
        });

        $('#frm_task').on('submit', function(eve){
            eve.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");
            $.ajax({
                type: 'POST',
                url: base_url +'/projects/'+ $('#task_projectid').val() +'/addtask',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#cover-spin').css('display', 'block');
                },
                success: function(response) {
                     console.log(response);
                    $('#cover-spin').css('display', 'none');
                    if(response.success == true){
                        alertify.success(response.message);
                        $('#mdlTask').modal('hide');
                        $('#frm_task')[0].reset();
                    }else{
                        alertify.error(response.message);
                    }
                },
                error: function(response) {
                    console.log(response);
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
