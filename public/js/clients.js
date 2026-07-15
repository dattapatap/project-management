$(document).ready(function () {
    $('#sales_executive').select2({
        dropdownParent: $("#mdlAssignTo")
    })

    $('#frm_asssign_to_opther').submit(function (e) {
        e.preventDefault();

        let client_id = $('#clientid').val();
        let executive = $('#sales_executive').val();
        if (client_id != '' || executive != '') {
            $.ajax({
                type: 'post',
                url: base_url + "/assignToExecutive",
                data: { 'executive': executive, 'clientid': client_id },
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.status == true) {
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

    $(document).on('click', '.assignToUser', function () {
        console.log('ccc');
        let client_id = $(this).attr('client');
        $('#sales_executive').empty().trigger("change");
        $.ajax({
            type: 'GET',
            url: base_url + "/users-by-team-members",
            data: { 'client': client_id },
            dataType: 'json',
            success: function (response) {
                if (response.status == true) {
                    $('#clientid').val(client_id);
                    let users = response.data;
                    users.forEach(element => {
                        var newOption = new Option(element.name, element.id, false, false);
                        $('#sales_executive').append(newOption).trigger('change');
                    });
                    $('#mdlAssignTo').modal('show');
                }
            },
        })

    })

    $(document).on('click', '.directMatureClient', function () {
        let client_id = $(this).attr('client');
        if (confirm('Are you sure you want to mark this client as matured directly?')) {
            $.ajax({
                type: 'POST',
                url: base_url + '/clients/' + client_id + '/direct-mature',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status == true) {
                        alertify.success(response.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        alertify.error(response.message);
                    }
                },
                error: function (response) {
                    alertify.error('Failed to mature client.');
                }
            });
        }
    });
})

// Project Creation
$(document).ready(function () {
    $('#mdlNewProject').modal({ show: false, backdrop: 'static' })
    $(document).on('click', '.createNewProject', function () {
        $('#mdlNewProject').modal('show');
        $('#client_name').val($(this).attr('clientnm'));
        $('#clientsid').val($(this).attr('client'));
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
        font_formats: "Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Book Antiqua=book antiqua,palatino; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino;",
        fontsize_formats: "8px 9px 10px 11px 12px 14px 16px 18px 20px 22px 24px 26px 28px 30px 32px 34px 36px 38px 40px 42px 44px 46px 48px 50px 52px 54px",
        height: 300
    });

    $(document).on('change', '#department', function () {
        let dept_value = $(this).val();
        $('#category').empty().append('<option selected="selected" value="">Select Category</option>');
        $.ajax({
            type: 'GET',
            url: base_url + "/projectcategory/subcategories",
            data: { 'projcategory': dept_value },
            success: function (response) {
                if (response.status == true) {
                    $("#category").select2({ data: response.data });
                }
            },
        });

        // Fetch Team Leaders
        $('#team_leader').empty().append('<option selected="selected" value="">Select Team Leader</option>');
        $.ajax({
            type: 'GET',
            url: base_url + "/projects/get-team-leaders",
            data: { 'category_id': dept_value },
            success: function (response) {
                if (response.status == true) {
                    let data = response.data.map(function (item) {
                        return { id: item.id, text: item.name };
                    });
                    $("#team_leader").select2({ data: data });
                    if (data.length === 1) {
                        $('#team_leader').val(data[0].id).trigger('change');
                    }
                }
            },
        });
    });

    $('#mdlNewProject').on('shown.bs.modal', function () {
        $('#department').trigger('change');
    });

    function getAllClients() {
        var category = $('#category_name').val();
        $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            bDestroy: true,
            pageLength: 50,
            ajax: {
                type: 'GET',
                url: base_url + "/clients",
                data: { 'category': category },
                error: function (err) { console.log(err); }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id', orderable: true, searchable: false, className: 'text-center' },
                { data: 'name', name: 'name', orderable: true, searchable: true },
                { data: 'mobile', name: 'mobile', orderable: false, searchable: true, className: 'text-center' },
                ...(isAuthority === 'true' || isAuthority === true ? [
                    { data: 'attribution', name: 'creator.name', orderable: true, searchable: true, className: 'text-center' }
                ] : []),
                { data: 'status_source', name: 'status', orderable: true, searchable: true, className: 'text-center' },
                { data: 'created_at', name: 'created_at', orderable: true, searchable: true, className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            order: [[isAuthority === 'true' || isAuthority === true ? 5 : 4, 'desc']], // Dynamic Sort index
            drawCallback: function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    }

    function formatHistory(d) {
        return '<div id="history-detail-' + d.id + '" class="p-3 bg-light rounded" style="border-left: 4px solid #7F00FF; margin: 10px 0; box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);">' +
               '   <div class="d-flex align-items-center justify-content-center p-3">' +
               '       <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div>' +
               '       <span class="font-weight-semibold text-dark font-size-13">Loading Touchpoint History...</span>' +
               '   </div>' +
               '</div>';
    }

    function loadHistoryAjax(clientId, placeholderId) {
        $.ajax({
            type: 'GET',
            url: base_url + "/client/history/bycategory",
            data: { 'client': clientId, 'category': 'STS' },
            dataType: 'json',
            success: function (response) {
                let container = $('#' + placeholderId);
                if (response.status === true && response.data.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-sm table-centered mb-0 bg-white shadow-sm rounded" style="font-size: 12.5px; border: 1px solid rgba(220, 220, 235, 0.6);">' +
                               '  <thead>' +
                               '    <tr style="background: #f4f6fc; color: #495057;">' +
                               '      <th class="p-2 font-weight-bold">Date & Time</th>' +
                               '      <th class="p-2 font-weight-bold">Followed By</th>' +
                               '      <th class="p-2 font-weight-bold">Touchpoint Status</th>' +
                               '      <th class="p-2 font-weight-bold" style="width: 40%;">Remarks / Discussion Summary</th>' +
                               '      <th class="p-2 font-weight-bold">Next Followup Schedule</th>' +
                               '    </tr>' +
                               '  </thead>' +
                               '  <tbody>';
                    response.data.forEach(function (h) {
                        let nextFollow = '-';
                        if (h.tbro) {
                            let timeStr = h.time ? ' at ' + h.time : '';
                            let formattedDate = new Date(h.tbro).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                            nextFollow = '<span class="badge badge-soft-danger font-size-11"><i class="mdi mdi-calendar-clock mr-1"></i>' + formattedDate + timeStr + '</span>';
                        }
                        let creatorName = h.referel ? h.referel.name : 'System';
                        let remarks = h.remarks ? h.remarks : 'N/A';

                        let dateStr = h.created_at ? new Date(h.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                        let timeCreated = h.created_at ? ' ' + new Date(h.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : '';

                        html += '<tr style="border-bottom: 1px solid rgba(230,230,245,0.7);">' +
                                '  <td class="p-2"><strong>' + dateStr + '</strong><br><small class="text-muted">' + timeCreated + '</small></td>' +
                                '  <td class="p-2"><span class="text-dark font-weight-semibold"><i class="mdi mdi-account-circle-outline text-muted mr-1"></i>' + creatorName + '</span></td>' +
                                '  <td class="p-2"><span class="badge badge-soft-info px-2 py-1">' + h.status + '</span></td>' +
                                '  <td class="p-2" style="white-space: normal; max-width: 300px; line-height: 1.5; color: #555;">' + remarks + '</td>' +
                                '  <td class="p-2">' + nextFollow + '</td>' +
                                '</tr>';
                    });
                    html += '  </tbody></table></div>';
                    container.html(html);
                } else {
                    container.html('<div class="text-center text-muted p-4 font-size-13"><i class="mdi mdi-information-outline mr-1 font-size-16 align-middle text-primary"></i>No touchpoint history logs found for this client.</div>');
                }
            },
            error: function () {
                $('#' + placeholderId).html('<div class="text-center text-danger p-4 font-size-13"><i class="mdi mdi-alert-circle-outline mr-1 font-size-16 align-middle"></i>Failed to fetch touchpoint history. Please try again.</div>');
            }
        });
    }

    $(document).on('click', '.status-trigger-wrapper', function (e) {
        e.stopPropagation();
        var tr = $(this).closest('tr');
        var table = $('#datatable').DataTable();
        var row = table.row(tr);
        var clientId = $(this).attr('data-client-id');
        var placeholderId = 'history-detail-' + clientId;

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown-history');
            $(this).find('.toggle-history-text').html('<i class="mdi mdi-chevron-down mr-1 text-primary"></i>History');
        } else {
            row.child(formatHistory({ id: clientId })).show();
            tr.addClass('shown-history');
            $(this).find('.toggle-history-text').html('<i class="mdi mdi-chevron-up mr-1 text-danger"></i>Close');
            loadHistoryAjax(clientId, placeholderId);
        }
    });

    getAllClients();

    $('#frm_create_new_project').submit(function (e) {
        e.preventDefault();
        var formData = new FormData($(this)[0]);
        $(".invalid-feedback").children("strong").text("");

        $.ajax({
            type: 'POST',
            url: base_url + '/client/createprojecct',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $(".creatBtn").html('Creating...');
                $(".creatBtn").prop('disabled', true);
            },
            success: function (response) {
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
            error: function (response) {
                $(".creatBtn").prop('disabled', false);
                $(".creatBtn").html('Create Project');
                if (response.responseJSON.status === 400) {
                    let errors = response.responseJSON.errors;
                    Object.keys(errors).forEach(function (key) {
                        $("#" + key + "Input").addClass("is-invalid");
                        $("#" + key + "-input-error").children("strong").text(errors[key][0]);
                    });
                }
            }

        });
    })

})


