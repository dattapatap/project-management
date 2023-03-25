@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Users</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Users List</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="float-right">
                <a href="{{ route('users.create')}}" type="button" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-plus"></i>
                    New Member
                </a>
            </div>
        </div>
        <div class="col-md-12 mt-2">
            <div class="card">
                <div class="card-body">
                    @if(!$users->isEmpty())

                        <div class="table-responsive">
                            <table class="table table-centered mb-0 table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col" style="width:5%">Sl No</th>
                                        <th scope="col" style="width: 15%;" >Name</th>
                                        <th scope="col" style="width: 25%;" >Email</th>
                                        <th scope="col"> Code</th>
                                        <th scope="col" > Role</th>
                                        <th scope="col" class="text-center"> Department</th>
                                        <th scope="col" style="width: 10%;" >Status</th>
                                        <th scope="col" class="width: 10%; text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse ($users as $items)
                                    <tr>
                                        <td> {{ $loop->index + 1  }} </td>
                                        <td> {{ $items->name }} </td>
                                        <td>{{ $items->email }} </td>
                                        <td>{{ $items->emp->mem_code }}   </td>
                                        <td>{{ $items->roles->pluck('name')[0] }}   </td>
                                        <td class="text-center">{{ $items->departments->dept->name }}   </td>
                                        <td>
                                            @if($items->status == 'Active')
                                                <a href="{{ route('users.changeStatus', $items->id ) }}" class="btn btn-success btn-sm">Active</a>
                                            @else
                                                <a href="{{ route('users.changeStatus', $items->id ) }}" class="btn btn-danger btn-sm">In-Active</a>
                                            @endif

                                        </td>
                                        <td class="text-center">

                                            @if($user->hasRole("Admin"))
                                                <form method="post" action="{{ route('users.destroy',[$items->id]) }}"  style="display: inline-block;">

                                                    <a type="button" class="btn btn-outline-warning btn-sm" href="{{ route('users.edit', $items->id ) }}"
                                                        data-toggle="tooltip" data-placement="bottom" title="Edit Member">
                                                        <i class="mdi mdi-square-edit-outline"></i>
                                                    </a>
                                                    &nbsp;
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm"  onclick="return confirm('Do you want to delete this Member?')"
                                                        data-toggle="tooltip" data-placement="bottom" title="Delete Member"><i class="mdi mdi-delete-outline"></i>
                                                    </button>

                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            No Users exist.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3 float-right">
                            {{ $users->links("pagination::bootstrap-4") }}
                        </div>

                    @else

                        <div class="col-md-12">
                            <div class="text-center">
                                <div class="mb-3" style="position: relative;">
                                    <img src="{{ asset('img/team.png') }}"
                                        style="height: 100%;width: 25%;"
                                        class="img-fluid rounded-circle" alt="">
                                </div>
                                <h3 class="text-truncate mb-2">You don't have any Members.</h3> <br>
                                <h6 class="fs-15">
                                <a href="{{ route('users.create')}}" class="btnAddDepartment text-success"> Click </a>
                                to create new Member
                                </h6>
                            </div>
                        </div>

                    @endif

                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
</div>
@endsection
