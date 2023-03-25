<div id="mdlAddVisitingCard" class="modal fade bs-example-modal-center" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Add Visiting Card</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_visiting_card" class="custom-validation"  method="POST" novalidate>
                    @csrf
                    <input type="hidden" value="{{ $client->id}}" name="client" id="client">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <input type="file" name="visiting_card" id="visiting_card" accept="image/*">
                                <span class="invalid-feedback" id="visiting_card-input-error" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row float-roght">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 float-right creatBtn">
                                Add
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('.add-visiting-card').click(function(){
                $('#mdlAddVisitingCard').modal('show');
        })

        $('#frm_visiting_card').submit(function(e){
            e.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");
            $.ajax({
                type: 'POST',
                url: '{{ route('client.addVisitingCard') }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".creatBtn").html('Uploading...');
                    $(".creatBtn").prop('disabled', true);
                },
                success: function(response) {
                    $('#frm_visiting_card')[0].reset();
                    alertify.success(response.message);
                    setTimeout(() => { window.location.reload(); }, 1000);
                },
                error: function(response) {
                    console.log(response);
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('Add');
                    if (response.responseJSON.status === 400) {
                        let errors = response.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            $("#" + key + "Input").addClass("is-invalid");
                            $("#" + key + "-input-error").children("strong").text(errors[key][0]);
                        });
                    }
                }
            });


        });
        $(".gallery-popup").magnificPopup({
            type:"image",closeOnContentClick:!0,mainClass:"mfp-fade",gallery:{enabled:!0,navigateByImgClick:!0,preload:[0,1]}
        });
    })
</script>
