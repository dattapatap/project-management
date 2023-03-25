function getAllDomains(){
    $("#datatable").DataTable({
        processing: true,
        serverSide: true,
        bDestroy: true,
        ajax :{
                type: 'GET',
                url: base_url+"/domains/getalldomains",
                error:function(err){ console.log(err);}
        },
        columns: [
            {data: 'DT_RowIndex', name: 'id', orderable: true, searchable: false},
            {data: 'name', name: 'clients.name', orderable: true, searchable: true},
            {data: 'domain', name: 'domain', orderable: false, searchable: true},
            {data: 'contactinfo', name: 'contactinfo', orderable: false, searchable: false},
            {data: 'mobile', name: 'clients.mobile', orderable: false, searchable: true},
            {data: 'registered_dt', name: 'registered_dt', orderable: true, searchable: true},
            {data: 'expiry_dt', name: 'expiry_dt', orderable: true, searchable: true},
            {data: 'action',  name: 'action', orderable: false, searchable: false },
        ]
    });
}

getAllDomains();

// Domain Creation
$(document).ready(function(){

    $('#mdlNewDomain').modal({show: false, backdrop: 'static'})
    $('#mdlUpdateDomain').modal({show: false, backdrop: 'static'})
    $('#mdlRenewDomain').modal({show: false, backdrop: 'static'})
    $('.createNewDomain').click(function(){
        $('#mdlNewDomain').modal('show');
    });

    $('#frm_update_domain').submit(function(e){
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
                $(".editBtn").html('UPDATING...');
                $(".editBtn").prop('disabled', true);
            },
            success: function(response) {
                console.log(response);
                if (response.status == true) {
                    $('#frm_update_domain')[0].reset();
                    alertify.success(response.message);
                    $('#mdlUpdateDomain').modal('hide');
                    window.location.reload();
                } else {
                    alertify.error(response.message);
                    $(".editBtn").prop('disabled', false);
                    $(".editBtn").html('UPDATE');
                }
            },
            error: function(response) {
                $(".editBtn").prop('disabled', false);
                $(".editBtn").html('UPDATE');
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

    $('#frm_new_domain').submit(function(e){
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
                $(".creatBtn").html('Submiting..');
                $(".creatBtn").prop('disabled', true);
            },
            success: function(response) {
                console.log(response);
                if (response.status == true) {
                    $('#frm_new_domain')[0].reset();
                    alertify.success(response.message);
                    $('#mdlNewDomain').modal('hide');
                    window.location.reload();
                } else {
                    alertify.error(response.message);
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('Add Domain');
                }
            },
            error: function(response) {
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

    $(document).on('click', '.editDomain', function(e){
        e.preventDefault();
        let clientid = $(this).attr('domainid');
        $.ajax({
            type: 'get',
            url: base_url +'/clientdomain/edit',
            data: {'domainid': clientid },
            dataType:'json',
            success: function(response) {
                if(response.status == true){
                     let domain = response.client;
                    $('#client_nm').val(domain.client_name);
                    $('#client_id').val(domain.client);
                    $('#domain_id').val(domain.id);
                    $('#domain').val(domain.domain);
                    $('#reg_date').val(domain.registered_dt);
                    $('#exp_date').val(domain.expiry_dt);
                    $('.editBtn').text('UPDATE');
                    $('#mdlUpdateDomain').modal('show');
                }
            },
        });


    })


    $(document).on('click', '.renewDomain', function(){
        $('#clientnm').val($(this).attr('clientnm'));
        $('#domainid').val($(this).attr('domainid'));
        $('#client').val($(this).attr('client'));
        $('#domain_nm').val($(this).attr('domainnm'));
        $('#mdlRenewDomain').modal('show');
    });

    $('#frm_renew_domain').submit(function(e){
        e.preventDefault();
        var formData = new FormData($(this)[0]);
        $(".invalid-feedback").children("strong").text("");
        $.ajax({
            type: 'POST',
            url: base_url +'/clientdomain/renew',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $(".btn-renew").html('Submiting..');
                $(".btn-renew").prop('disabled', true);
            },
            success: function(response) {
                if (response.status == true) {
                    $('#frm_renew_domain')[0].reset();
                    alertify.success(response.message);
                    $('#mdlRenewDomain').modal('hide');
                    getAllDomains();
                } else {
                    alertify.error(response.message);
                    $(".btn-renew").prop('disabled', false);
                    $(".btn-renew").html('Renew Domain');
                }
            },
            error: function(response) {
                $(".btn-renew").prop('disabled', false);
                $(".btn-renew").html('Renew Domain');
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
