@extends('layouts.app')
<style>
    .h1, h1 {
        font-size: 2.0rem;
    }
    h1 i{
        color: #00000075;
        font-size: 1.5rem;
        /* color: aqua; */
    }
    .bts i, h4, span{
        color:     #646f7a;
    }
    .user-dropdown span{
        color: #e9ecef;
    }

    .TabsHeader .nav-link{
        padding: 0.5rem 1.5rem;
    }
    .tab-content .me-3{
        margin-right: 2rem !important;
    }
    .tab-content .d-flex{
        border-bottom: 1px solid #74788d30;
    }
    .unread{
        background-color: rgba(0,154,224,.08);
    }
    .read{
        background-color: white;
    }

</style>

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Notifications</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>

                        <li class="breadcrumb-item active">Notifications</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-lg-12 mt-4" style="min-height: 500px;">
        <div class="row">
            <div class="offset-2 col-md-8">
                <div class="card" style="margin-bottom: 10px;">
                    <div class="card-body" style="padding: 0.5rem 1.25rem;">
                        <div class="row">
                            <div class="col-md-12 mt-2">
                                <h5 class="float-left"> Notifications </h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                            <div class="row">
                                @if($notification->where('read_at', null)->count() > 0)
                                    <div class="col-md-12 marksall" style="">
                                        <a href="#" class="btn btn-default mark-all float-right">
                                            <span>Mark all as read </span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                @forelse($notification as $items)
                                        <div class="notifications @if($items->unread()) unread @else  read @endif" style="border-bottom: 1px solid #c2bbbb;">
                                            <a href="{{ $items->data['link'] }}" class="text-reset notification-item  @if($items->unread()) mark-as-read @endif" data-id="{{ $items->id }}"  >
                                                <div class="media">
                                                    <img src="{{ Avatar::create($items->data['category'])->toBase64()  }}" class="mr-3 rounded-circle avatar-xs" alt="user-pic">
                                                    <div class="media-body">
                                                        <h6 class="mt-0 mb-1" style="color: #090909;">{!! htmlspecialchars_decode($items->data['header']) !!}</h6>
                                                        <div class="font-size-12 text-muted">
                                                            <p class="mb-1">{!! htmlspecialchars_decode($items->data['data']) !!}</p>
                                                            <p class="mb-0"><i class="mdi mdi-clock-outline"></i> {{ Carbon\Carbon::parse($items->created_at)->diffForHumans() }} </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>

                                @empty
                                <div class="col-sm-12">
                                        <br>
                                        <div class="text-center">
                                            <div class="mb-3" style="position: relative;">
                                                <img src="{{ asset('assets/images/notification.jpg') }}"
                                                    style="height: 100%;width: 25%;"
                                                    class="img-fluid" alt="">
                                            </div>
                                            <p class="text-muted text-truncate mb-2">You don't have any notifications to display here
                                            </p>
                                        </div>
                                    </div>
                                @endforelse

                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function(){
        $(function() {
            $('.mark-as-read').click(function(e) {
                let id = $(this).data('id');
                var div = $(this);
                $.ajax({
                        type: 'POST',
                        url: '{{ route('mark-as-read-notification') }}',
                        data: {id},
                        success: function(response) {
                            console.log('Succes!',response);
                        },
                        error : function(err) {
                            console.log('Error!', err.responseText);
                        },
                    });
            });

            $('.mark-all').click(function(e) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('mark-all-as-read-notification') }}',
                    data:{id:null},
                    success: function(response) {
                        console.log(response);
                        $("div.unread").removeClass("unread");
                        $("div.notifications").addClass("read");
                        $('.marksall').css('display', 'none');
                    },
                    error : function(err) {
                        console.log('Error!', err.responseText);
                    },
                });
            });


        });
    })
</script>
@endsection
