@extends('layouts.app')

@section('content')


<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Logs</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Logs</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered mb-0 table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" style="width: 100px;">Sl No</th>
                                    <th scope="col">User</th>
                                    <th scope="col">IP Address</th>
                                    <th scope="col">Login Time</th>
                                    <th scope="col">Logout Time</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $items)
                                <tr>
                                    <td> {{ $loop->index + 1  }} </td>
                                    <td> {{ $items->users->name }} </td>
                                    <td>{{ $items->id_add }} </td>
                                    <td>{{ $items->login }}   </td>
                                    <td>{{ $items->logout }}</td>
                                    <td>
                                        <a type="button" class="btn btn-outline-success btn-sm viewActivities" logid="{{$items->id}}"
                                            href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" title="View Activities">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        No Logs exist.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="row mt-3 float-right">
                        {{ $logs->links("pagination::bootstrap-4") }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div id="mdlactivity" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="myLargeModalLabel">Log Activities</h5>
                    <button type="button" class="close btnClose" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="height: 500px;overflow-y: auto;">
                    <table class="table table-striped mb-0 tblActivity">
                        <thead class="thead-light">
                            <th>Sl No.</th>
                            <th>Time</th>
                            <th>Modal </th>
                            <th>Description </th>
                            <th>Activity</th>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div><!-- /.modal-content -->
        </div>
    </div>



    <!-- end row -->
</div>
@endsection

@section('scripts')
<script>
    $('.viewActivities').click(function(){
        let act_id = $(this).attr('logid');
        $.ajax({
            url: "/logs/"+act_id,
            type: "get",
            success: function(response) {
                if(response.status == true){
                    let act = response.data;
                    $('.tblActivity tbody').empty();
                    let content = '';
                    for(let i=0; i< act.length; i++){
                        console.log(act[i]);
                        content +='<tr><td>'+ (i+1) +'</td><td>'+act[i].ch_date+'</td><td>'+ act[i].mo_modal+ '</td><td>'+ act[i].desc+ '</td>'+
                                '<td>'+ act[i].status +'</td></tr>';
                    }
                    $('#mdlactivity').modal('show');
                    $('.tblActivity tbody').append(content);
                }else{
                    $('#mdlactivity').modal('show');
                    $('.tblActivity tbody').append('<tr><td colspan="5" style="text-align:center;"> No Activity Found</td></tr>')
                }
            },
            error: function(result) {
                console.log(result);
            }
        });
    })

    $('.btnClose').click(function(){
        $('#mdlactivity').modal('hide');
        $('.tblActivity tbody').empty();
    })

</script>
@endsection

