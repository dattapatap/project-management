<div id="mdlEditProject" class="modal fade" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Edit Project:</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_edit_project" class="custom-validation"  method="POST">
                    @csrf
                    <input type="hidden" value="" name="project-id" id="project-id">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label> Client <span class="text_required">*</span></label>
                                <input type="text" class="form-control" name="client_name" id="client_name" placeholder="Client" readonly tabindex="1">
                                <span class="invalid-feedback" id="client_name-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label> Project Name <span class="text_required">*</span></label>
                                <input type="text" class="form-control" name="project_name" id="project_name" placeholder="Project Name" tabindex="2">
                                <span class="invalid-feedback" id="project_name-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-4">
                            <label> Est. Start Date <span class="text_required">*</span></label>
                            <div class="form-group">
                                <input type="date" class="form-control" name="start_date" id="start_date" placeholder="Start Date" tabindex="3">
                                <span class="invalid-feedback" id="start_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-4">
                            <label> Est. End Date <span class="text_required">*</span></label>
                            <div class="form-group">
                                <input type="date" class="form-control" name="end_date" id="end_date" placeholder="End Date" tabindex="4">
                                <span class="invalid-feedback" id="end_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-4">
                            <label> Actual Start Date <span class="text_required">*</span></label>
                            <div class="form-group">
                                <input type="date" class="form-control" name="act_start_date" id="act_start_date" placeholder="Start Date" tabindex="5">
                                <span class="invalid-feedback" id="act_start_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 float-right">
                            <label> Project Description <span class="text_required">*</span></label>
                            <textarea class="form-control" name="description" id="description" tabindex="4"></textarea>
                            <span class="invalid-feedback" id="description-input-error" role="alert"> <strong></strong></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"
                                > Update Project </button>
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
                selector: 'textarea#description',
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

        $(document).on('click', '.btn_edit_project', function(eve){
            eve.preventDefault();
            let projectid = $(this).attr('projectid');
            $.ajax({
                type: 'GET',
                url: base_url +'/projects/'+ projectid +'/edit',
                dataType:'json',
                beforeSend: function() {
                    $('#cover-spin').css('display', 'block');
                },
                success: function(response) {
                    $('#cover-spin').css('display', 'none');
                    if(response.success == true){
                        const project = response.project
                        $('#project-id').val(project.id)
                        $('#client_name').val(project.clients.name)
                        $('#project_name').val(project.project_name)
                        $('#start_date').val(moment(project.start_date).format('YYYY-MM-DD'))
                        $('#end_date').val(moment(project.end_date).format('YYYY-MM-DD'))
                        $('#act_start_date').val(moment(project.act_start_date).format('YYYY-MM-DD'))
                        if(project.description != null ){
                            tinymce.get("description").setContent(project.description);
                        }

                        $('#mdlEditProject').modal('show');
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

        $('#frm_edit_project').on('submit', function(eve){
            eve.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");

            $.ajax({
                type: 'POST',
                url: base_url +'/projects/'+ $('#projectid').val() +'/update',
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
                        $('#mdlEditProject').modal('hide');
                        $('#frm_edit_project')[0].reset();
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
