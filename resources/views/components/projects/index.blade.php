@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Projects</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Project List</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="float-right">
                <a href="{{ route('users.create')}}" type="button" class="btn btn-primary btn-md btn-rounded">
                    <i class="mdi mdi-plus"></i>
                    New Project
                </a>
            </div>
        </div>
        <div class="col-md-12 mt-2">

            @if(!$projects->isEmpty())


                @foreach ($projects as $items)
                <div class="col-md-3">
                    <div class="card card-project">
                        <div class="card-body">
                            <div class="project-card-header">
                                <h6 class="project-name">Website Development</h6>
                                <a class="btn-action-project" href="javascript:void(0);">
                                    <i class="mdi mdi-account-circle-outline"></i>
                                </a>
                            </div>
                            <div class="project-card-body">

                            </div>
                        </div>
                    </div>

                </div>

                @endforeach

                {{-- <div class="row mt-3 float-right">
                    {{ $projects->links("pagination::bootstrap-4") }}
                </div> --}}

            @else
                <div class="col-md-12">
                    <div class="text-center">
                        <div class="mb-3" style="position: relative;">
                            <img src="{{ asset('img/projects.jpg') }}"
                                style="height: 100%;width: 25%;"
                                class="img-fluid rounded-circle" alt="">
                        </div>
                        <h3 class="text-truncate mb-2">You don't have any Projects.</h3> <br>
                        <h6 class="fs-15">
                        <a href="#" class="btnAddDepartment text-success"> Click </a>
                        to create new Project
                        </h6>
                    </div>
                </div>
            @endif

        </div>
    </div>
    <!-- end row -->
</div>
@endsection
