@extends('layouts.app')

@section('content')

<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="pb-2 d-flex align-items-center justify-content-between">
                <a href="{{ url('client/Fresh')  }}" class="btn-back" >
                    <i class="mdi mdi-keyboard-backspace fs-20"></i>
                </a>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">

        <div class="card card-top-border cw-100">
            <div class="card-body">
                <!-- Header company details -->
                @include('components.clients.history.header')

                <ul class="nav nav-tabs nav-dept mt-3" role="tablist">

                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('clients/'.base64_encode($client->id).'/'.'contacts' ) }}" >
                            <span class="d-none d-md-inline-block">Contacts</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" @if($user->hasRole([1,2,3,4,5])) href="{{ url('clients/'.base64_encode($client->id).'/'.'sts' ) }}" @endif role="tab">
                            <span class="d-none d-md-inline-block">STS</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" @if($user->hasRole([1,2,3,4,5])) href="{{ url('clients/'.base64_encode($client->id).'/'.'dsr' ) }}" @endif
                            role="tab">
                            <span class="d-none d-md-inline-block">DSR</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            @if($user->hasRole([1,3,4,6])) href="{{ url('clients/'.base64_encode($client->id).'/'.'development' ) }}" @endif role="tab">
                            <span class="d-none d-md-inline-block">Development</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            @if($user->hasRole([1,3,4,7])) href="{{ url('clients/'.base64_encode($client->id).'/'.'designing' ) }}" @endif  role="tab">
                            <span class="d-none d-md-inline-block">Designing</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            @if($user->hasRole([1,3,4,8])) href="{{ url('clients/'.base64_encode($client->id).'/'.'seo' ) }}" @endif role="tab">
                            <span class="d-none d-md-inline-block">Digital Marketing</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0)" role="tab">
                            <span class="d-none d-md-inline-block">History</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('clients/'.base64_encode($client->id).'/'.'docs' ) }}" role="tab">
                            <span class="d-none d-md-inline-block">Documents</span>
                        </a>
                    </li>

                    @if($client->is_active)
                    <li class="nav-item">
                        <a class="nav-link"  @if($user->hasRole([1,2,3,4,5])) href="{{ url('clients/'.base64_encode($client->id).'/'.'payment' ) }}" @endif role="tab">
                            <span class="d-none d-md-inline-block">Payment</span>
                        </a>
                    </li>
                    @endif

                </ul>

                <!-- Tab panes -->
                <div class="tab-content p-3">
                    <div class="tab-pane active" role="tabpanel">
                        <div class="heading_tab_content d-flex">
                            <h4 class="lbl-heading-pane fs-15"> History</h4>
                            <select class="form-control" name="history_category" id="history_category" style="width: 25%">
                                <option selected value> Select History Category</option>
                                <option value="STS"> STS</option>
                                <option value="DSR"> DSR</option>
                                <option value="Dev"> Development</option>
                                <option value="Design"> Designing</option>
                                <option value="DigMar"> Digital Marketing</option>
                            </select>
                            <span>
                                <span id="pane-timer"> <?= date('M d Y h:m:s')?></span>
                                <i class="mdi mdi-calendar-month"></i>
                            </span>
                        </div>
                        <hr>
                        <div class="history-content">
                            <div class="col-12">
                                <ul class="history-lst">

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- end row -->
</div>


@include('components.clients.history.visitingcard')

@endsection

@section('scripts')
<script>
    $(document).ready(function(){
        $('#history_category').change(function(){
            let category = $(this).val();
            if(category != ''){
                $('.history-lst').empty();
                $.ajax({
                    type: 'GET',
                    url: "{{ route('client.history') }}",
                    data: {'category' : category, 'client':{{ $client->id }} },
                    success : function(response) {
                        if(response.status){
                            let hst = response.data;
                            hst.forEach(item => {
                                if(item.tbro != null){
                                   var tbro =  'TBRO DATE: '+ moment(item.tbro).format('Do MMM YYYY');
                                }else{
                                   var tbro ='';
                                }
                                let hstText = '<li style="display: grid">'+
                                                    '<span>'+ moment(item.created_at).format('Do MMM YYYY h:mm:ss A')+', Updated By '+ item.referel.name + '</span>'+
                                                    '<span class="pl-3">Remarks: '+ item.remarks +'</span>'+
                                                    '<span class="pl-3">Status: '+ item.status +' </span>'+
                                                    '<span class="pl-3">Added Time: '+ item.time +' ' + tbro +  '  </span>'+
                                                '</li>';

                                $('.history-lst').append(hstText);
                            });
                        }else{
                            console.log('text');
                            $('.history-lst').html('<div class="text-center fs-18" style="text-align:center;justify-content:center;align-item:center;"> No Histroy Exist</div>');
                        }
                    },
                  });


            }
        })


    })
</script>
@endsection
