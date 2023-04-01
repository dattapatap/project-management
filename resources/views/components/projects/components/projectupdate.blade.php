<div id="mdlProjectUpdate" class="modal fade" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Project Update:</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_project_update" class="custom-validation"  method="POST">
                    @csrf
                    <input type="hidden" value="" name="projectid" id="projectid">
                    <div class="row">
                        <div class="col-md-12 float-right">
                            <label> Update History <span class="text_required">*</span></label>
                            <textarea class="form-control" name="remarks" id="remarks" tabindex="1" placeholder="Project Remark"></textarea>
                            <span class="invalid-feedback" id="remarks-input-error" role="alert"> <strong></strong></span>
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
        $(document).on('click', '.btn_project_update', function(eve){
            $('#projectid').val($(this).attr('projectid'))
            $('#mdlProjectUpdate').modal('show');
        });

        $('#frm_project_update').on('submit', function(eve){
            eve.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");
            $.ajax({
                type: 'POST',
                url: base_url +'/projects/'+ $('#projectid').val() +'/projectupdate',
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
                        $('#mdlProjectUpdate').modal('hide');
                        $('#frm_project_update')[0].reset();
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
