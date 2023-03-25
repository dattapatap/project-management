@extends('layouts.app')

@section('content')

<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Departments</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Departments</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="btn-group mr-1 mt-1 mb-2 float-right">
                <button type="button" class="btn btn-primary btn-sm btnAddDepartment">
                    <i class="mdi mdi-plus"></i>
                    New Department
                </button>
            </div>
        </div>
    </div>
    <div class="row mt-4 departments">
        @forelse ($departments as $item)
            <div class="col-4">
                <div class="card project card-top-border">
                    <div class="card-body">
                        <div class="department">
                            <div class="department-header">
                                <a href="{{ route('departments.show', $item->name) }}">
                                    <h5 class="department-title mt-1">
                                        {{ $item->name  }} <span class="badge badge-pill badge-primary">{{ $item->branch->code }}</span>
                                    </h5>
                                </a>
                                <div class="btn-group float-right">
                                    <a href="#" class="dropdown-toggle arrow-none"
                                        data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 21px;">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-start">
                                        <a class="dropdown-item btn_edit_department" dept_id="{{ $item->id }}">
                                                <i class="mdi mdi-pencil"></i> Edit
                                        </a>
                                        {{-- <form method="post" action="{{ route('departments.destroy',[ $item->id ]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item btn_upload_student"  onclick="return confirm('Do you want to delete this item?')"
                                                data-toggle="tooltip" data-placement="bottom" title="Delete Department"><i class="mdi mdi-delete-outline"></i>
                                                Delete
                                            </button>
                                        </form> --}}
                                    </div>
                                </div>
                            </div>
                            <div class="">
                                <div class="department-members">
                                    @if(!$item->users->isEmpty())
                                        <ul class="department-users">
                                            <?php  $totCount = 0; ?>
                                            @foreach ($item->users as $members)
                                                @if($totCount < 10)
                                                    {{-- @php
                                                        $memUser = DB::table('users')->where('id', $members->user)->first();
                                                        $totCount++;
                                                    @endphp --}}
                                                    <li>
                                                        @if ($members->userdetail->profile)
                                                            <img title="{{ $members->userdetail->name }}" src="{{ asset('storage/'. $members->userdetail->profile )}}">
                                                        @else
                                                            <img title="" src="{{ Avatar::create($members->userdetail->name)->toBase64()  }}">
                                                        @endif

                                                    </li>
                                                @endif
                                            @endforeach
                                            @php
                                                $count = $item->users()->count();
                                            @endphp
                                            @if( $count > 10 )
                                                <li class="count">{{ $count - 10 }}+</li>
                                            @endif
                                        </ul>
                                    @else
                                        <ul class="department-users">
                                            <li>
                                                <img title="Dont have members" src="{{ asset('img/users.png')}}">
                                            </li>
                                        </ul>
                                    @endif
                                </div>

                                @if ($item->status == true)
                                    <span class="badge badge-success float-right">Active</span>
                                @else
                                    <span class="badge badge-danger float-right">In Active</span>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-md-12">
                <div class="text-center">
                    <div class="mb-3" style="position: relative;">
                        <img src="{{ asset('img/department.png') }}"
                            style="height: 100%;width: 20%;"
                            class="img-fluid rounded-circle" alt="">
                    </div>
                    <h3 class="text-truncate mb-2">You don't have any Department.</h3> <br>
                    <h6 class="fs-15">
                    <a href="javascript:void(0);" class="btnAddDepartment text-success"> Click </a>
                    to create new Department
                    </h6>
                </div>
            </div>

        @endforelse
    </div>

    <div class="row mt-3 float-right">
        {{ $departments->links("pagination::bootstrap-4") }}
    </div>
    <!-- end row -->
</div>

<div id="mdlDepartment" class="modal fade bs-example-modal-center" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0"></h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_department" class="custom-validation"  method="POST" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <input type="hidden" id="department_id" name="department_id" value="">
                            <div class="form-group">
                                <label>Name <span class="text_required">*</span> </label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Name">
                                <span class="invalid-feedback" id="name-input-error" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Branch <span class="text_required">*</span> </label>
                                <select type="text" name="branch" id="branch" class="form-control" style="width: 100%">
                                    <option value selected> Select Branch</option>
                                    @foreach ($branches as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} </option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback" id="branch-input-error" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Description <span class="text_required">*</span></label>
                                <input type="text" name="description" id="description" class="form-control" placeholder="Description">
                                <span class="invalid-feedback" id="description-input-error" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row float-roght">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 float-right creatBtn">
                                Create
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')

<script>
    $(document).ready(function(){
        $('.btnAddDepartment').click(function(){
            $('#mdlDepartment').modal('show');
            $('.modal-title').text('New Department')
            $(".invalid-feedback").children("strong").text("");
        })
        $('.btnmdlclose').click(function(){
            $('#mdlDepartment').modal('hide');
        })

        $('#frm_department').submit(function(e){
            e.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");

            $.ajax({
                type: 'POST',
                url: '{{ route('departments.store') }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".creatBtn").html('please wait..');
                    $(".creatBtn").prop('disabled', true);
                },
                success: function(response) {
                    console.log(response);
                    if (response.status == true) {
                        $('#frm_department')[0].reset();
                        alertify.success(response.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);

                    } else {
                        alertify.error(response.message);
                        $(".creatBtn").prop('disabled', false);
                        $(".creatBtn").html('Submit');
                    }

                },
                error: function(response) {
                    console.log(response);
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('Submit');
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

        $('.btn_edit_department').click(function(e){
            let dept_id = $(this).attr('dept_id')
            console.log(dept_id);
            $.ajax({
                type: 'GET',
                url: 'departments/' + dept_id + '/edit',
                success: function(response) {
                    if(response.status == true){
                        let dept = response.data;
                        $('.modal-title').text('Edit Department')
                        $('.creatBtn').text('Update');

                        $('#department_id').val(dept.id);
                        $('#name').val(dept.name);
                        $('#description').val(dept.description);
                        $('#branch').val(dept.branchid);
                        $('#mdlDepartment').modal('show');
                    }else{
                        alertify.error(response.message);
                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
        });



    })

</script>

@endsection
