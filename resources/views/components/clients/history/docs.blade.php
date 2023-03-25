@extends('layouts.app')

@section('content')
<style>
        .custom-file-upload {
            border: 1px solid #ccc;
            display: inline-block;
            padding: 6px 12px;
            cursor: pointer;
        }
        .bar {
            background-color: #B4F5B4;
            width: 0%;
            height: 25px;
            border-radius: 3px;
        }

        .percent {
            position: absolute;
            display: inline-block;
            top: 0px;
            left: 47%;
            color: #7F98B2;
        }
        .badge:empty {
            display: block;
        }

        .dropdown-item:focus,
        .dropdown-item:hover {
            color: #ffffff;
            text-decoration: none;
            background-color: #fa9a2c;
        }

        .progress {
            position: relative;
            width: 100%;
            border: 1px solid #7F98B2;
            padding: 1px;
            border-radius: 3px;
            height: 1rem !important;
        }

</style>

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
                        <a class="nav-link" href="{{ url('clients/'.base64_encode($client->id).'/'.'history' ) }}" role="tab">
                            <span class="d-none d-md-inline-block">History</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active"  href="javascript:void(0)" role="tab">
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
                            <h4 class="lbl-heading-pane fs-15"> Documents Update</h4>
                            <div class="adddocs">
                                <a class="text-info add-doc-btn fs-14" style="cursor: pointer;">
                                    <i class="mdi mdi-plus-circle-outline"></i> Add Document
                                </a>
                            </div>
                            <div>
                                <span id="pane-timer"> <?= date('M d Y h:m:s')?></span>
                                <i class="mdi mdi-calendar-month"></i>
                            </div>
                        </div>
                        <hr>
                        <div class="section_gallery">
                            <div class="row">
                                @php
                                    $docs = DB::table('client_docs')->where('client', $client->id)->get();
                                @endphp

                                @forelse ($docs as $item)
                                    @php
                                        $ext = pathinfo(public_path().'/storage/clients/'.$item->files, PATHINFO_EXTENSION);
                                    @endphp

                                    <div class="content_docs col-2">
                                        @if($ext == 'pdf')
                                            <img src="{{ asset('img/pdf.png') }}" wi dth='100%' height='100%'>
                                        @elseif($ext == 'mp4' || $ext == 'mkv')
                                            <img src="{{ asset('img/mp4.png') }}" width='100%' height='100%'>
                                        @elseif($ext == 'zip' || $ext == '7zip')
                                            <img src="{{ asset('img/zip.jpg') }}" width='100%' height='100%'>
                                        @elseif($ext == 'rar')
                                            <img src="{{ asset('img/rar.png') }}" width='100%' height='100%'>
                                        @else
                                            <img src="{{ asset('storage/'.$item->files) }}" width='100%' height='100%'>
                                        @endif

                                        <div class="img-btn">
                                            <a href="{{ route('docs.download', $item->id ) }}" data-toggle="tooltip" data-placement="bottom" title="Download">
                                                <i class="mdi mdi-arrow-down-bold-circle fs-20"></i>
                                            </a>
                                        </div>
                                        <div class="docs-text">
                                                <span>
                                                    @if($item->doc_type!='')
                                                        {{ $item->doc_type }}
                                                    @else
                                                         {{ $item->description }}
                                                    @endif
                                                </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-md-12">
                                        <div class="text-center">
                                            <div class="mb-3" style="position: relative;">
                                                <img src="{{ asset('img/docs.jpg') }}"
                                                    style="height: 100%;width: 20%;"
                                                    class="img-fluid rounded-circle" alt="">
                                            </div>
                                            <h4 class="text-truncate mb-2">You don't have any docs.</h4> <br>
                                            <h6 class="fs-15">
                                                <a href="" class="btnAddDepartment text-success"> Click </a>to add new
                                            </h6>
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
    <!-- end row -->
</div>

@include('components.clients.history.visitingcard')


<div id="mdlAddDocs" class="modal fade bs-example-modal-center" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Add New Document</h5>
                <button type="button" class="close btn_close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_docs" class="custom-validation"  method="POST" novalidate>
                    @csrf
                    <input type="hidden" value="{{ $client->id}}" name="client" id="client">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <select class="form-control" name="dock_type" id="dock_type" required>
                                    <option value selected> Select Document Type</option>
                                    <option value="development"> Development</option>
                                </select>
                                <span class="invalid-feedback" id="dock_type-input-error" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="" class=" "> Description </label>
                                <input type="text" id="description" name='description' class="form-control" required>
                                <span class="invalid-feedback" id="description-input-error" role="alert">  <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label> Select a file to upload <span class="file_text_type">( Max 2GB)</span></label>
                            <br>
                            <div class="col-md-12 onselect" style="display: none;">
                                <em class="file_name_text mr-3"></em>
                                &nbsp;&nbsp;
                                <input type="button" value="remove" name="remove"
                                    class="btn btn-sm btn-danger btn_remove ml-2">
                            </div>
                            <div class="col-md-12 upload_div">
                                <label for="file_name" class="btn btn-outline-info ">
                                    Upload file
                                </label>
                                <input type="file" id="file_name" name='file_name' style="display:none;">
                                <span class="invalid-feedback" id="file_title-input-error" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 progress progress_div mt-1 mb-0" style="display: none;">
                        <div class="progress-bar bg-primary" id='progress-bar' role="progressbar" style="width:0%;">0%
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 float-right progress_div selectDive" style="display: none;">
                            <span class="fl_status"> uploading...</span>
                            <a class="btn btn-sm btn-pause" data-toggle="tooltip" title="Pause"><i
                                    class="mdi mdi-pause-circle" style="font-size: 20px;"></i></a>
                            <a class="btn btn-sm btn-start" data-toggle="tooltip" title="Resume"><i
                                    class="mdi mdi-play" style="font-size: 20px;"></i></a>
                        </div>
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="button" class="btn btn-default btn-md btn_close"> Cancel </button>
                                &nbsp;&nbsp;
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit"
                                    disabled> Upload </button>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3 btns_div_done" style="display: none;">
                            <div class="float-right">
                                <button type="button" class="btn btn-success btn-md btn_end"> Done </button>
                            </div>
                        </div>
                    </div>




                </form>
            </div>
        </div>
    </div>
</div>



@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
<script src="{{ asset('assets/js/resumable.js')}}"></script>
@endsection
