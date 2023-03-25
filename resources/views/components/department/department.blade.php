@extends('layouts.app')

@section('content')


<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="pb-2 d-flex align-items-center justify-content-between">
                    <a href="{{ url()->previous()  }}" class="btn-back" >
                        <i class="mdi mdi-keyboard-backspace fs-20"></i>
                    </a>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/departments') }}">Department</a></li>
                        <li class="breadcrumb-item active">{{ $departments->name }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="row">

        <div class="card card-top-border cw-100" style="width: 100%">
            <div class="card-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs nav-dept" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0)" role="tab">
                            <i class="mdi mdi-account-multiple-outline mr-1 align-middle"></i> <span class="d-none d-md-inline-block">Members</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"  href="{{ url('departments/'. $departments->name .'/'.'teams' ) }}"  role="tab">
                            <i class="mdi mdi-account-group-outline mr-1 align-middle"></i> <span class="d-none d-md-inline-block">Teams</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content p-3">
                    <div class="tab-pane active" id="members" role="tabpanel">
                            @if(!$departments->users->isEmpty())
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="width:5%">Sl</th>
                                            <th scope="col" style="width: 8%;" >Avatar</th>
                                            <th scope="col" style="width: 25%;" >Name</th>
                                            <th scope="col" style="width: 15%;"> Code</th>
                                            <th scope="col" style="width: 15%;"> Role</th>
                                            <th scope="col" style="width: 15%;"> Designation</th>
                                            <th scope="col" style="width: 10%;"> From Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach ($departments->users as $items)
                                            @php
                                                $users = \App\Models\User::with('emp')
                                                            ->where('id', $items->user)->first();
                                            @endphp
                                            <tr>
                                                <td> {{ $loop->index + 1  }} </td>
                                                <td class="text-center" style="padding: 5px;">
                                                    @if ($users->profile)
                                                       <img title="{{ $users->name }}" src="{{ asset('storage/'. $users->profile )}}"
                                                        style="width: 35px;height: 35px;border-radius: 50%;">
                                                   @else
                                                       <img title="" src="{{ Avatar::create($users->name)->toBase64()  }}"
                                                       style="width: 35px;height: 35px;border-radius: 50%;">
                                                   @endif
                                               </td>
                                                <td> {{ $users->name }}
                                                    @if($users->hasRole("Team-Leader")) <span class="badge badge-warning">Team-Leader</span> @endif
                                                    @if($users->hasRole("Project-Manager")) <span class="badge badge-success">Project-Manager</span> @endif
                                                </td>
                                                <td>{{ $users->emp->mem_code }}   </td>
                                                <td>{{ $users->roles->pluck('name')[0] }}   </td>
                                                <td>{{ $users->emp->designation }}   </td>
                                                <td>{{ Carbon\Carbon::parse($items->from_date)->format('d M Y') }}   </td>

                                                {{-- <td class="text-center">
                                                    @if(Auth::user()->hasRole(["Admin"]))
                                                        <form method="post" action="{{ route('department.deletemember',[$items->id]) }}"  style="display: inline-block;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-danger btn-sm"  onclick="return confirm('Do you want to delete this Member from department?')"
                                                                data-toggle="tooltip" data-placement="bottom" title="Delete Member"><i class="mdi mdi-delete-outline"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td> --}}
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                                <div class="col-md-12">
                                    <div class="text-center">
                                        <div class="mb-3" style="position: relative;">
                                            <img src="{{ asset('img/users.png') }}"
                                                style="height: 100%;width: 20%;"
                                                class="img-fluid rounded-circle" alt="">
                                        </div>
                                        <h3 class="text-truncate mb-2">You don't have any Members.</h3> <br>
                                        <h6 class="fs-15">
                                        <a href="javascripts:void(0)" class="btnAddMembers text-success"> Click </a>
                                        to add new Member
                                        </h6>
                                    </div>
                                </div>
                            @endif
                    </div>
                </div>

            </div>
        </div>

    </div>
    <!-- end row -->
</div>




@endsection
