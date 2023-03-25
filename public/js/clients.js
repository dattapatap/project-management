$(document).ready(function(){
    $('#sales_executive').select2({
        dropdownParent: $("#mdlAssignTo")
    })

    $('#frm_asssign_to_opther').submit(function(e){
        e.preventDefault();

        let client_id = $('#clientid').val();
        let executive = $('#sales_executive').val();
        if(client_id !='' || executive !=''){
            $.ajax({
                type:'post',
                url:base_url+"/assignToExecutive",
                data: { 'executive':executive, 'clientid':client_id},
                dataType : 'json',
                success:function(response){
                    console.log(response);
                    if(response.status == true){
                        alertify.success(response.message);
                        $('#mdlAssignTo').modal('hide');
                        setTimeout(() => {
                                window.location.reload();
                        }, 500);
                    }
                },
            })
        }

    })

    $(document).on('click','.assignToUser', function(){
        console.log('ccc');
        let client_id = $(this).attr('client');
        $('#sales_executive').empty().trigger("change");
        $.ajax({
            type:'GET',
            url: base_url +"/users-by-team-members",
            data: { 'client': client_id},
            dataType : 'json',
            success:function(response){
                if(response.status == true){
                    $('#clientid').val(client_id);
                    let users = response.data;
                    users.forEach(element => {
                        var newOption = new Option( element.name, element.id, false, false);
                        $('#sales_executive').append(newOption).trigger('change');
                    });
                    $('#mdlAssignTo').modal('show');
                }
            },
        })

    })
})

// Project Creation
$(document).ready(function(){
    $('#mdlNewProject').modal({show: false, backdrop: 'static'})
    $(document).on('click', '.createNewProject', function(){
        $('#mdlNewProject').modal('show');
        $('#client_name').val($(this).attr('clientnm'));
        $('#client-id').val($(this).attr('client'));
    });

    tinymce.init({
            selector: 'textarea#description',
            branding: false,
            plugins: [
                "advlist autolink lists link image charmap print preview anchor",
                "searchreplace visualblocks code fullscreen",
                "insertdatetime media table paste codesample"
            ],
            toolbar: "undo redo | fontselect styleselect fontsizeselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | codesample action section button",
            font_formats:"Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Book Antiqua=book antiqua,palatino; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino;",
            fontsize_formats: "8px 9px 10px 11px 12px 14px 16px 18px 20px 22px 24px 26px 28px 30px 32px 34px 36px 38px 40px 42px 44px 46px 48px 50px 52px 54px",
            height: 300
    });

    $(document).on('change', '#department', function(){
        let dept_value = $(this).val();
        $('#category').empty().append('<option selected="selected" value="">Select Category</option>');
        $.ajax({
            type: 'GET',
            url: base_url + "/department/category",
            data: {'deptid' : dept_value },
            success: function(response) {
                if(response.status = true){
                    $("#category").select2({data :response.data });
                }else{

                }
            },
        });
    })

    $('#frm_create_new_project').submit(function(e){
        e.preventDefault();
        var formData = new FormData($(this)[0]);
        $(".invalid-feedback").children("strong").text("");

        $.ajax({
            type: 'POST',
            url: base_url +'/client/createprojecct',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $(".creatBtn").html('Creating...');
                $(".creatBtn").prop('disabled', true);
            },
            success: function(response) {
                console.log(response);
                if (response.status == true) {
                    $('#frm_create_new_project')[0].reset();
                    alertify.success(response.message);
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('Create Project');
                    $('#mdlNewProject').modal('hide');
                } else {
                    alertify.error(response.message);
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('Create Project');
                }
            },
            error: function(response) {
                $(".creatBtn").prop('disabled', false);
                $(".creatBtn").html('Create Project');
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


// Domain Creation
$(document).ready(function(){
    $('#mdlNewDomain').modal({show: false, backdrop: 'static'})
    $(document).on('click', '.createNewDomain', function(){
        $('#mdlNewDomain').modal('show');
        $('#client_nm').val($(this).attr('clientnm'));
        $('#client_id').val($(this).attr('client'));
    });

    $('#frm_create_new_domain').submit(function(e){
        e.preventDefault();
        var formData = new FormData($(this)[0]);
        $(".invalid-feedback").children("strong").text("");

        $.ajax({
            type: 'POST',
            url: base_url +'/clientdomain/store',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $(".creatBtn").html('Submiting...');
                $(".creatBtn").prop('disabled', true);
            },
            success: function(response) {
                console.log(response);
                if (response.status == true) {
                    $('#frm_create_new_domain')[0].reset();
                    alertify.success(response.message);
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('Add Domain');
                    $('#mdlNewDomain').modal('hide');
                } else {
                    alertify.error(response.message);
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('Add Domain');
                }
            },
            error: function(response) {
                console.log(response);
                $(".creatBtn").prop('disabled', false);
                $(".creatBtn").html('Add Domain');
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


