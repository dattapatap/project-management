@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">{{ $project->project_name }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Taskbar</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <div class="row">

    </div>

    <div class="row">





    </div>
    <!-- end row -->
</div>
 @endsection

@section('component')

@include('components.projects.components.assigntoteam')

@include('components.projects.components.editproject')

@include('components.projects.components.projectupdate')

@include('components.projects.components.projecttask')


@endsection
