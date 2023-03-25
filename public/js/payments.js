function getAllPayents(){
    $("#payments").DataTable({
        processing: true,
        serverSide: true,
        bDestroy: true,
        ajax :{
                type: 'GET',
                url: base_url+"/payments/getallpayments",
                error:function(err){ console.log(err);}
        },
        columns: [
            {data: 'DT_RowIndex', name: 'id', orderable: true, searchable: false},
            {data: 'clients', name: 'clients.name', orderable: true, searchable: true},
            {data: 'projects', name: 'projects.project_name', orderable: false, searchable: true},
            {data: 'package', name: 'package', orderable: false, searchable: true},
            {data: 'paid', name: 'paid', orderable: false, searchable: false},
            {data: 'balance', name: 'balance', orderable: true, searchable: true},
            {data: 'addedby', name: 'addedby.name', orderable: true, searchable: true},
            {data: 'action',  name: 'action', orderable: false, searchable: false },
        ]
    });
}



// Domain Creation
$(document).ready(function(){
    getAllPayents();

    $(document).on('click', '.payHistory', function(){
        let packageid = $(this).attr('packageid');
        $.ajax({
            type: 'get',
            url: base_url +'/payments/getpayments-by-package',
            data: {'packageid': packageid },
            dataType:'json',
            success: function(response) {
                $('#tbl_payment_history tbody tr').empty();
                if(response.status == true){
                    let arrPayment = response.payments;
                    arrPayment.forEach((item, index) => {
                        let fl ='';
                        if(item.payment_type == 'Online'){
                            fl = '<span class="text-success"> '+ item.transactioinid + '</span>';
                        }else{
                            fl = '<a  href="storage/'+ item.file +'" target="_blank" class="p-1 view-visiting-card gallery-popup">'+
                                        '<img  src="storage/'+ item.file +'" style="height:35px;"  />'+
                                      '</a>';
                        }

                        let tr = '<tr>'+
                                    '<td>'+ (index+1) +'</td>'+
                                    '<td>'+ moment(item.paid_date).format('DD MMM YYYY') +'</td>'+
                                    '<td>'+ formatter.format(item.amount) +'</td>'+
                                    '<td>'+ formatter.format(item.remains) +'</td>'+
                                    '<td>'+ item.payment_type +'</td>'+
                                    '<td>'+ fl +'</td>'+
                                    '<td>'+ item.added_by.name +'</td>'+
                                '</tr>';

                        $('#tbl_payment_history tbody').append(tr);

                    });
                    $('#mdlPaymentHistory').modal('show');

                }else{
                    $('#tbl_payment_history tbody').append('<tr> <td colspan="7" class="text-center"> No Payment entry exist! </td> </tr>');
                    $('#mdlPaymentHistory').modal('show');
                }
            },
        });

    })

    $(document).on('click', '.addNewEntry', function(){
        let project  = $(this).attr('projectid');
        let client  = $(this).attr('clientid');
        $.ajax({
            type: 'GET',
            url: base_url +"/client/payment/byProjecct",
            data: {'project': project},
            success: function(response) {
                $('#client').val(client)
                $('#project_type').val(project)

                $('#balance').val(response.balance)
                $('#mdlAddPayment').modal('show');
            }
        });

    });



    $('#payment_type').change(function(){
        let transactiontype = $(this).val();
        if(transactiontype == 'Cheque'){
            $('.pay_type_online').css('display', 'none');
            $('.pay_type_cash').css('display', 'none');
            $('.pay_type_cheque').css('display', 'block');
        }else if(transactiontype == 'Cash'){
            $('.pay_type_online').css('display', 'none');
            $('.pay_type_cheque').css('display', 'none');
            $('.pay_type_cash').css('display', 'block');
        }else if(transactiontype == 'Online'){
            $('.pay_type_cash').css('display', 'none');
            $('.pay_type_cheque').css('display', 'none');
            $('.pay_type_online').css('display', 'block');
        }else{
            $('.pay_type_cash').css('display', 'none');
            $('.pay_type_cheque').css('display', 'none');
            $('.pay_type_online').css('display', 'none');
        }
    })

    $('#frm_add_payments').on('submit', function(e){
        e.preventDefault();
        var formData = new FormData($(this)[0]);
        $(".invalid-feedback").children("strong").text("");

        $.ajax({
            type: 'POST',
            url: base_url + '/client/payment/add',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $(".creatBtn").html('Submiting...');
                $(".creatBtn").prop('disabled', true);
            },
            success: function(response) {
                if (response.status == true) {
                    $('#frm_add_payments')[0].reset();
                    alertify.success(response.message);
                    setTimeout(() => { window.location.reload();}, 1500);
                } else {
                    alertify.error(response.message);
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('Add Payment');
                }
            },
            error: function(response) {
                $(".creatBtn").prop('disabled', false);
                $(".creatBtn").html('Add Payment');
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


    var formatter = new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 2,
    });


})
