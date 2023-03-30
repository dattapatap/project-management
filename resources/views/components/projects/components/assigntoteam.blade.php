<div id="mdlAssignToTeam" class="modal fade bs-example-modal-center" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Assign Project</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_assign" class="custom-validation"  method="POST" novalidate>
                    @csrf
                    <input type="hidden" value="" name="project_id" id="project_id">
                    @php
                        $teams = DB::table('teams')->where('department', 2)->orderBy('name', 'asc')->get();
                    @endphp
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Select Team</label>
                               <select class="form-control select2" width="100%" name="team" id="team">
                                    <option value="">Choose Team</option>
                                    @foreach ($teams as $item)
                                        <option value="{{ $item->id }}"> {{ $item->name }} </option>
                                    @endforeach
                               </select>
                                <span class="invalid-feedback" id="team-input-error" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row float-roght">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 float-right creatBtn">
                                Assign
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


@section('scripts')
<script>
    $(document).ready(function(){

        $('.btn_assign_project').click(function(eve){
            eve.preventDefault();
            let projectid = $(this).attr('projectid');
            $('#project_id').val(projectid);
            $('#mdlAssignToTeam').modal('show');
        })

        $('#frm_assign').submit(function(e){
            e.preventDefault();
            $(".invalid-feedback").children("strong").text("");
            $.ajax({
                type: 'POST',
                url: base_url +'/projects/assignToTeam',
                data: {'project': $('#project_id').val(), 'team': $('#team').val()},
                dataType:'json',
                beforeSend: function() {
                    $(".creatBtn").prop('disabled', true);
                },
                success: function(response) {
                    $('#frm_assign')[0].reset();
                    alertify.success(response.message);
                    setTimeout(() => { window.location.reload(); }, 1000);
                },
                error: function(response) {
                    console.log(response);
                    $(".creatBtn").prop('disabled', false);
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



    })

</script>

@endsection
