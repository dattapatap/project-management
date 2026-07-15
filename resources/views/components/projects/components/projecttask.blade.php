<style>
    #mdlTask .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        font-family: 'Outfit', 'Inter', sans-serif;
    }

    #mdlTask .modal-header {
        background: linear-gradient(135deg, #f8fafd 0%, #edf2f9 100%);
        border-bottom: 1px solid #edf2f7;
        padding: 20px 24px;
    }

    #mdlTask .modal-title {
        font-weight: 700;
        color: #1e293b;
        font-size: 1.15rem;
    }

    #mdlTask .modal-body {
        padding: 24px;
    }

    #mdlTask label {
        font-weight: 600;
        font-size: 13px;
        color: #475569;
        margin-bottom: 6px;
    }

    #mdlTask .form-control {
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

    #mdlTask .form-control:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    #mdlTask textarea.form-control {
        height: auto;
    }

    #mdlTask .creatBtn {
        background: linear-gradient(135deg, #34c38f 0%, #2ca97b 100%);
        border: none;
        color: #ffffff;
        font-weight: 600;
        padding: 10px 24px;
        border-radius: 10px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(52, 195, 143, 0.25);
    }

    #mdlTask .creatBtn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(52, 195, 143, 0.35);
    }

    #mdlTask .select2-container--default .select2-selection--single {
        height: 44px !important;
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #f8fafc !important;
        padding-top: 7px !important;
    }

    #mdlTask .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
    }

    .bootstrap-datetimepicker-widget {
        z-index: 99999 !important;
    }
</style>

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
                <form id="frm_task" class="custom-validation" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" value="" name="task_projectid" id="task_projectid">
                    <div class="row">
                        <div class="col-md-6" id="project_select_container" style="display: none; margin-bottom: 15px;">
                            <div class="form-group">
                                <label> Select Project <span class="text_required">*</span></label>
                                <select class="form-control select2" id="shortcut_project_select" style="width: 100%">
                                    <option value="">Select Project...</option>
                                </select>
                                <span class="invalid-feedback" id="task_projectid-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-md-12" id="task_title_container" style="margin-bottom: 15px;">
                            <div class="form-group">
                                <label> Task Title <span class="text_required">*</span></label>
                                <input type="text" class="form-control" name="task_title" id="task_title" placeholder="Task Title" tabindex="1">
                                <span class="invalid-feedback" id="task_title-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-3">
                            <label> Priority <span class="text_required">*</span></label>
                            <div class="form-group">
                                <select class="form-control" name="task_priority" id="task_priority" placeholder="Status" tabindex="2">
                                    <option value="Low"> Low </option>
                                    <option value="Medium" selected> Medium </option>
                                    <option value="High"> High </option>
                                </select>
                                <span class="invalid-feedback" id="task_priority-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label> Est. Start Date <span class="text_required">*</span></label>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="task_est_start_date" id="task_est_start_date"
                                        placeholder="DD-MM-YYYY hh:mm AM/PM" tabindex="3" autocomplete="off">
                                    <div class="input-group-append task_est_start_date_icon" style="cursor: pointer;">
                                        <span class="input-group-text"><i class="mdi mdi-calendar text-primary"></i></span>
                                    </div>
                                </div>
                                <span class="invalid-feedback" id="task_est_start_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label> Est. End Date <span class="text_required">*</span></label>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="task_est_end_date" id="task_est_end_date"
                                        placeholder="DD-MM-YYYY hh:mm AM/PM" tabindex="4" autocomplete="off">
                                    <div class="input-group-append task_est_end_date_icon" style="cursor: pointer;">
                                        <span class="input-group-text"><i class="mdi mdi-calendar text-primary"></i></span>
                                    </div>
                                </div>
                                <span class="invalid-feedback" id="task_est_end_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="form-group">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="mb-0"> Assign To <span class="text_required">*</span></label>
                                    @if(Auth::user()->hasRole('Team-Leader'))
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="delegate_task_check" name="is_inter_team" value="1">
                                        <label class="custom-control-label font-size-11 text-warning font-weight-bold" for="delegate_task_check" style="cursor: pointer;">Delegate</label>
                                    </div>
                                    @endif
                                </div>
                                <select class="form-control select2" name="task_user" id="task_user" tabindex="5">
                                    <option value="" selected>Loading Employees...</option>
                                </select>
                                <span class="invalid-feedback" id="task_user-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label> Task Description <span class="text_required">*</span></label>
                            <textarea class="form-control" name="task_description" id="task_description" tabindex="6"></textarea>
                            <span class="invalid-feedback" id="task_description-input-error" role="alert"> <strong></strong></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"
                                    tabindex="7"> Add Task </button>
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

        if (typeof tinymce !== 'undefined') {
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
                font_formats: "Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Book Antiqua=book antiqua,palatino; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino;",
                fontsize_formats: "8px 9px 10px 11px 12px 14px 16px 18px 20px 22px 24px 26px 28px 30px 32px 34px 36px 38px 40px 42px 44px 46px 48px 50px 52px 54px",
                height: 300
            });
        }

        function formatProjectOption(option) {
            if (!option.id) {
                return option.text;
            }
            var status = $(option.element).data('status');
            var iconHtml = '';

            if (status === 'ToDo') {
                iconHtml = '<i class="mdi mdi-checkbox-blank-circle text-danger" style="margin-right: 8px;" title="ToDo"></i>';
            } else if (status === 'InProgress') {
                iconHtml = '<i class="mdi mdi-checkbox-blank-circle text-info" style="margin-right: 8px;" title="InProgress"></i>';
            } else if (status === 'Completed') {
                iconHtml = '<i class="mdi mdi-checkbox-blank-circle text-success" style="margin-right: 8px;" title="Completed"></i>';
            }

            return '<span class="d-inline-flex align-items-center">' + iconHtml + option.text + '</span>';
        }

        $('#task_est_start_date').datetimepicker({
            format: 'DD-MM-YYYY hh:mm A',
            minDate: moment().startOf('day'),
            icons: {
                time: 'mdi mdi-clock-outline',
                date: 'mdi mdi-calendar',
                up: 'mdi mdi-chevron-up',
                down: 'mdi mdi-chevron-down',
                previous: 'mdi mdi-chevron-left',
                next: 'mdi mdi-chevron-right'
            }
        });
        $('#task_est_end_date').datetimepicker({
            format: 'DD-MM-YYYY hh:mm A',
            minDate: moment().startOf('day'),
            icons: {
                time: 'mdi mdi-clock-outline',
                date: 'mdi mdi-calendar',
                up: 'mdi mdi-chevron-up',
                down: 'mdi mdi-chevron-down',
                previous: 'mdi mdi-chevron-left',
                next: 'mdi mdi-chevron-right'
            }
        });

        $("#task_est_start_date").on("dp.change", function(e) {
            $('#task_est_end_date').data("DateTimePicker").minDate(e.date || moment().startOf('day'));
        });

        function loadEmployees(projectId, interTeam) {
            if ($('#task_user').hasClass("select2-hidden-accessible")) {
                $('#task_user').select2('destroy');
            }
            $('#task_user').empty().append('<option value="" selected>Loading...</option>');
            $.ajax({
                type: 'GET',
                url: '/projects/get-employees',
                data: {
                    'project_id': projectId,
                    'inter_team': interTeam ? 1 : 0
                },
                success: function(response) {
                    if (response.status == true) {
                        if ($('#task_user').hasClass("select2-hidden-accessible")) {
                            $('#task_user').select2('destroy');
                        }
                        $('#task_user').empty();
                        response.data.forEach(function(emp) {
                            $('#task_user').append('<option value="' + emp.id + '">' + emp.name + '</option>');
                        });
                        $('#task_user').trigger('change');
                        $('#task_user').select2({
                            dropdownParent: $('#mdlTask')
                        });
                    }
                },
                error: function() {
                    alertify.error('Failed to load employees');
                }
            });
        }

        $(document).on('click', '.add-task, .btn_add_task, .btn_header_add_task', function(e) {
            $('#frm_task')[0].reset();
            if (typeof tinymce !== 'undefined' && tinymce.get("task_description")) {
                tinymce.get("task_description").setContent('');
            }
            $('#delegate_task_check').prop('checked', false);

            let projectId = $(this).attr('projectid');
            if (projectId) {
                // Opened from project page
                $('#project_select_container').hide();
                $('#task_title_container').removeClass('col-md-6').addClass('col-md-12');
                $('#task_projectid').val(projectId);
                loadEmployees(projectId, false);
                $('#mdlTask').modal('show');
            } else {
                // Opened from shortcut header
                $('#project_select_container').show();
                $('#task_title_container').removeClass('col-md-12').addClass('col-md-6');
                $('#task_projectid').val('');
                if ($('#task_user').hasClass("select2-hidden-accessible")) {
                    $('#task_user').select2('destroy');
                }
                $('#task_user').empty().append('<option value="">Select Project First</option>');
                $('#task_user').select2({
                    dropdownParent: $('#mdlTask')
                });

                // Load projects list
                if ($('#shortcut_project_select').hasClass("select2-hidden-accessible")) {
                    $('#shortcut_project_select').select2('destroy');
                }
                $('#shortcut_project_select').empty().append('<option value="" selected>Loading Projects...</option>');
                $.ajax({
                    type: 'GET',
                    url: '/projects/active-list',
                    success: function(response) {
                        if (response.status == true) {
                            if ($('#shortcut_project_select').hasClass("select2-hidden-accessible")) {
                                $('#shortcut_project_select').select2('destroy');
                            }
                            $('#shortcut_project_select').empty().append('<option value="">Select Project...</option>');
                            response.data.forEach(function(proj) {
                                $('#shortcut_project_select').append('<option value="' + proj.id + '" data-status="' + proj.status + '">' + proj.project_name + '</option>');
                            });
                            $('#shortcut_project_select').select2({
                                dropdownParent: $('#mdlTask'),
                                templateResult: formatProjectOption,
                                templateSelection: formatProjectOption,
                                escapeMarkup: function(m) {
                                    return m;
                                }
                            });
                        }
                    },
                    error: function() {
                        alertify.error('Failed to load active projects');
                    }
                });

                $('#mdlTask').modal('show');
            }
        });

        $(document).on('change', '#shortcut_project_select', function() {
            let projectId = $(this).val();
            $('#task_projectid').val(projectId);
            if (projectId) {
                loadEmployees(projectId, $('#delegate_task_check').is(':checked'));
            } else {
                if ($('#task_user').hasClass("select2-hidden-accessible")) {
                    $('#task_user').select2('destroy');
                }
                $('#task_user').empty().append('<option value="">Select Project First</option>');
                $('#task_user').select2({
                    dropdownParent: $('#mdlTask')
                });
            }
        });

        $(document).on('change', '#delegate_task_check', function() {
            let projectId = $('#task_projectid').val();
            if (projectId) {
                loadEmployees(projectId, this.checked);
            }
        });

        $(document).on('click', '.task_est_start_date_icon', function() {
            $('#task_est_start_date').focus();
        });
        $(document).on('click', '.task_est_end_date_icon', function() {
            $('#task_est_end_date').focus();
        });

        $('#frm_task').on('submit', function(eve) {
            eve.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");
            $.ajax({
                type: 'POST',
                url: '/projects/' + $('#task_projectid').val() + '/addtask',
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
                    if (response.success == true) {
                        alertify.success(response.message);
                        $('#mdlTask').modal('hide');
                        $('#frm_task')[0].reset();
                        window.location.reload()
                    } else {
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
