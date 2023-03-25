$(document).ready(function(){
    let app_url = $('meta[name="app_url"]').attr('content')

    $('.add-doc-btn').click(function(){
        $('#mdlAddDocs').modal('show');
    });
    $('.btn_close').click(function() {
        $("#frm_docs")[0].reset();
        $('#mdlAddDocs').modal('hide');
        hideProgress();
        if (resumable != null) {
            removeChunkFiles();
            resumable.cancel();
        }
        window.location.reload();
    })


    let resumable;
    $('#file_name').change(function() {
        var file = $('#file_name')[0].files[0].name;
        $('.file_name_text').text(file);
        $('.onselect').show();
        $(this).parent('div').hide();
        $('.btn-submit').removeAttr('disabled', false);
    });

    //Progress Div
    let progress = $('.progress_div');

    function showProgress() {
        progress.find('.progress-bar').css('width', '0%');
        progress.find('.progress-bar').html('0%');
        progress.find('.progress-bar').removeClass('bg-success');
        progress.show();
    }
    function updateProgress(value) {
        progress.find('.progress-bar').css('width', `${value}%`)
        progress.find('.progress-bar').html(`${value}%`)
    }
    function hideProgress() {
        progress.hide();
    }
    $('.btn-pause').click(function() {
        $('.fl_status').text('paused');
    })
    $('.btn-start').click(function() {
        resumable.upload();
        $('.fl_status').text('uploading...');
    })
    $('.btn_remove').click(function() {
        $('.upload_div').show();
        $(this).parent('div').hide();
        $('#file_name').val('');
        progress.hide();
        resumable.cancel();
    })

    $('.btn_end').click(function() {
        window.location.reload();
    })


    function validateForm() {
        var status = true;
        var dock_type = $('#dock_type').val();
        var description = $('#description').val();
        var file = $('#file_name').get(0).files.length;
        if (description == '' || dock_type =='' || file == 0) {
            status = false;
            if (description == '') {
                $("#description-input-error").children("strong").text("Name is required field");
            }
            if (dock_type == '') {
                $("#dock_type-input-error").children("strong").text("Name is required field");
            }
            if (file === 0) {
                $("#file_title-input-error").children("strong").text("File is required field");
            }
        }
        return status;
    }


    $('#frm_docs').submit(function(e) {
        e.preventDefault();
        $(".invalid-feedback").children("strong").text("");
        $("#frm_docs input").removeClass("is-invalid");
        var clientid = $('#client').val();
        var doc_type = $('#dock_type').val();
        var description = $('#description').val();

        let browseFile = $('#file_name')[0].files[0];
        // check validations
        if (validateForm() == true) {
            resumable = new Resumable({
                target: app_url +'/client/docs/uploadFile',
            });
            resumable.opts.query = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                'clientid': clientid,
                'doctype': doc_type,
                'description': description,
            }; // CSRF token
            resumable.opts.fileType = ['mp4', 'mkv','pdf', 'png', 'jpg', 'gif', 'jpeg'];  //'zip', 'rar', '7zip',
            resumable.opts.uploadMethod = 'POST';
            resumable.opts.headers = {
                'Accept': 'application/json'
            };
            resumable.opts.testChunks = false;
            resumable.opts.throttleProgressCallbacks = 1;
            resumable.opts.chunkSize = 1 * 1024 * 1024;
            resumable.opts.maxFileSize = 2000 * 1024 * 1024;
            resumable.addFile(browseFile);
            resumable.on('fileAdded', function(file) {
                showProgress();
                resumable.upload();
            });
            resumable.on('fileProgress', function(file) { // trigger when file progress update
                updateProgress(Math.floor(file.progress() * 100));
            });
            resumable.on('fileSuccess', function(file, response) { // trigger when file upload complete
                console.log(response);
                // if (response.status == true) {
                    $('.selectDive').hide();
                    $('.onselect').hide();
                    $('.btns_div').hide();
                    $('.btn-submit').prop('disbled', true);
                    $('.btns_div_done').show();
                // }
            });
            resumable.on('fileError', function(file, response) { // trigger when there is any error
                $('.btn-submit').removeAttr('disabled');
                alert('file uploading error.')
            });
            resumable.on('beforeCancel', function() {
                removeChunkFiles();
            });
            $('.btn-submit').prop('disabled', true);

        } else {
            alert('validation error');
        }
    });

    function removeChunkFiles() {
        $.ajax({
            type: 'GET',
            url: app_url +'/client/docs/removechunck',
            cache: false,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log(response);
            },
            error: function(response) {
                console.log(response.responseText);
            }
        });
    }
})

