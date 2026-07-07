<style>
    #mdleditTask .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        font-family: 'Outfit', 'Inter', sans-serif;
    }
    #mdleditTask .modal-header {
        background: linear-gradient(135deg, #f8fafd 0%, #edf2f9 100%);
        border-bottom: 1px solid #edf2f7;
        padding: 20px 24px;
    }
    #mdleditTask .modal-title {
        font-weight: 700;
        color: #1e293b;
        font-size: 1.15rem;
    }
    #mdleditTask .modal-body {
        padding: 24px;
    }
    #mdleditTask label {
        font-weight: 600;
        font-size: 13px;
        color: #475569;
        margin-bottom: 6px;
    }
    #mdleditTask .form-control {
        height: 44px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #334155;
        background-color: #f8fafc;
        transition: all 0.2s ease-in-out;
    }
    #mdleditTask .form-control:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
    }
    #mdleditTask textarea.form-control {
        height: auto;
    }
    #mdleditTask .creatBtn {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border: none;
        color: #ffffff;
        font-weight: 600;
        padding: 10px 24px;
        border-radius: 10px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
    }
    #mdleditTask .creatBtn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);
    }
    #mdleditTask .select2-container--default .select2-selection--single {
        height: 44px !important;
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #f8fafc !important;
        padding-top: 7px !important;
    }
    #mdleditTask .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
    }
    .bootstrap-datetimepicker-widget {
        z-index: 99999 !important;
    }
</style>

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
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txt_task_est_start_date" id="txt_task_est_start_date"
                                    placeholder="DD-MM-YYYY hh:mm AM/PM" autocomplete="off" >
                                    <div class="input-group-append txt_task_est_start_date_icon" style="cursor: pointer;">
                                        <span class="input-group-text"><i class="mdi mdi-calendar text-primary"></i></span>
                                    </div>
                                </div>
                                <span class="invalid-feedback" id="txt_task_est_start_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label> Est. End Date <span class="text_required">*</span></label>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="txt_task_est_end_date" id="txt_task_est_end_date"
                                    placeholder="DD-MM-YYYY hh:mm AM/PM" autocomplete="off" >
                                    <div class="input-group-append txt_task_est_end_date_icon" style="cursor: pointer;">
                                        <span class="input-group-text"><i class="mdi mdi-calendar text-primary"></i></span>
                                    </div>
                                </div>
                                <span class="invalid-feedback" id="txt_task_est_end_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="form-group">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="mb-0"> Assign To <span class="text_required">*</span></label>
                                     @if(Auth::user()->hasRole('Team-Leader'))
                                     <div class="custom-control custom-checkbox">
                                         <input type="checkbox" class="custom-control-input" id="txt_delegate_task_check" name="is_inter_team" value="1">
                                         <label class="custom-control-label font-size-11 text-warning font-weight-bold" for="txt_delegate_task_check" style="cursor: pointer;">Delegate</label>
                                     </div>
                                     @endif
                                </div>
                                <select  class="form-control select2" name="txt_task_user" id="txt_task_user" >
                                    <option value="" selected>Loading Employees...</option>
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

        $('#txt_task_est_start_date').datetimepicker({
            format: 'DD-MM-YYYY hh:mm A',
            icons: {
                time: 'mdi mdi-clock-outline',
                date: 'mdi mdi-calendar',
                up: 'mdi mdi-chevron-up',
                down: 'mdi mdi-chevron-down',
                previous: 'mdi mdi-chevron-left',
                next: 'mdi mdi-chevron-right'
            }
        });
        $('#txt_task_est_end_date').datetimepicker({
            format: 'DD-MM-YYYY hh:mm A',
            icons: {
                time: 'mdi mdi-clock-outline',
                date: 'mdi mdi-calendar',
                up: 'mdi mdi-chevron-up',
                down: 'mdi mdi-chevron-down',
                previous: 'mdi mdi-chevron-left',
                next: 'mdi mdi-chevron-right'
            }
        });

        function loadEditEmployees(projectId, interTeam, selectedId) {
            $('#txt_task_user').empty().append('<option value="" selected>Loading...</option>');
            $.ajax({
                type: 'GET',
                url: "{{ route('projects.employees') }}",
                data: { 
                    'project_id': projectId,
                    'inter_team': interTeam ? 1 : 0
                },
                success: function(empResponse) {
                    if (empResponse.status == true) {
                        $('#txt_task_user').empty();
                        empResponse.data.forEach(function(emp) {
                            let selected = (emp.id == selectedId) ? 'selected' : '';
                            $('#txt_task_user').append('<option value="' + emp.id + '" ' + selected + '>' + emp.name + '</option>');
                        });
                        $('#txt_task_user').trigger('change');
                        $('#txt_task_user').select2({
                            dropdownParent: $('#mdleditTask')
                        });
                    }
                }
            });
        }

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
                        $('#txt_task_est_start_date').val(moment(task.startdate).format('DD-MM-YYYY hh:mm A'))
                        $('#txt_task_est_end_date').val(moment(task.enddate).format('DD-MM-YYYY hh:mm A'))
                        
                        $('#txt_delegate_task_check').prop('checked', response.is_assigned_to_tl);
                        $('#txt_delegate_task_check').data('project-id', task.projectid);
                        $('#txt_delegate_task_check').data('selected-user', task.assigned_to);

                        loadEditEmployees(task.projectid, response.is_assigned_to_tl, task.assigned_to);

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

        $('#txt_delegate_task_check').change(function() {
            let projectId = $(this).data('project-id');
            let selectedUser = $(this).data('selected-user');
            if (projectId) {
                loadEditEmployees(projectId, this.checked, this.checked ? null : selectedUser);
            }
        });

        $(document).on('click', '.txt_task_est_start_date_icon', function() {
            $('#txt_task_est_start_date').focus();
        });
        $(document).on('click', '.txt_task_est_end_date_icon', function() {
            $('#txt_task_est_end_date').focus();
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
