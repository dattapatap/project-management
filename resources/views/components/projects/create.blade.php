@extends('layouts.app')

@section('styles')
<style>
    .project-create-container {
        font-family: 'Outfit', 'Inter', sans-serif;
    }

    .project-create-container .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.04);
        background: #ffffff;
    }

    .project-create-container .card-header {
        background: linear-gradient(135deg, #f8fafd 0%, #edf2f9 100%);
        border-bottom: 1px solid #edf2f7;
        padding: 20px 24px;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }

    .project-create-container .card-header h4 {
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        font-size: 1.15rem;
    }

    .project-create-container .card-body {
        padding: 30px 24px;
    }

    .project-create-container label {
        font-weight: 600;
        font-size: 13px;
        color: #475569;
        margin-bottom: 6px;
    }

    .project-create-container label span.text_required {
        color: #ef4444;
    }

    .project-create-container .form-control {
        height: 44px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #334155;
        background-color: #f8fafc;
        transition: all 0.2s ease-in-out;
    }

    .project-create-container .form-control:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .project-create-container textarea.form-control {
        height: auto;
    }

    .project-create-container .btn-primary {
        background: linear-gradient(135deg, #556ee6 0%, #3b82f6 100%);
        border: none;
        color: #ffffff;
        font-weight: 600;
        padding: 10px 24px;
        border-radius: 10px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(85, 110, 230, 0.25);
    }

    .project-create-container .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(85, 110, 230, 0.35);
        background: linear-gradient(135deg, #4458cc 0%, #2563eb 100%);
    }

    .project-create-container .select2-container--default .select2-selection--single {
        height: 44px !important;
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #f8fafc !important;
        padding-top: 7px !important;
    }

    .project-create-container .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
    }
</style>
@endsection

@section('content')

<div class="container-fluid project-create-container">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="pb-2 d-flex align-items-center justify-content-between">
                    <a href="{{ url('/projects') }}" class="btn-back">
                        <i class="mdi mdi-keyboard-backspace fs-20"></i>
                    </a>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/projects') }}">Projects</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title"> New Project </h4>
                </div>
                <div class="card-body">
                    <form id="frm_create_new_project" class="custom-validation" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-6">
                                <label> Project Name <span class="text_required">*</span></label>
                                <input type="text" class="form-control" name="project_name" id="project_name" placeholder="Enter Project Name" required tabindex="1">
                                <span class="invalid-feedback" id="project_name-input-error" role="alert"> <strong></strong></span>
                            </div>
                            <div class="col-6">
                                <label class="d-flex justify-content-between align-items-center mb-1">
                                    <span>Client <span class="text_required">*</span></span>
                                    <button type="button" class="btn btn-sm btn-success py-0 px-2" data-toggle="modal" data-target="#addClientModal" title="Add New Client">
                                        <i class="mdi mdi-plus"></i>
                                    </button>
                                </label>
                                <select class="form-control select2" name="clientsid" id="clientsid" style="width: 100%;">
                                    <option selected value> Select Client</option>
                                    @foreach ($clients as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback" id="clientsid-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-4">
                                <label> Project Department( Category ) <span class="text_required">*</span></label>
                                @php
                                $departments = DB::table('project_category')->where('deleted_at', null)->orderBy('id', 'asc')->get();
                                $userDeptId = optional($user->departments)->department;
                                @endphp
                                <select class="form-control select2" name="department" id="department" width="100%">
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $item)
                                    <option value="{{ $item->id }}"> {{ $item->category }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback" id="department-input-error" role="alert"><strong></strong></span>
                            </div>

                            <div class="col-4">
                                <label> Sub Category </label>
                                <div class="form-group">
                                    <select class="form-control select2" name="category" id="category" style="width:100%" tabindex="3">
                                        <option selected value> Select Sub Category</option>
                                    </select>
                                    <span class="invalid-feedback" id="category-input-error" role="alert"><strong></strong></span>
                                </div>
                            </div>

                            <div class="col-4">
                                <label> Team Leader </label>
                                <select class="form-control select2" name="{{ $user->hasRole('Team-Leader') ? '' : 'team_leader' }}" id="team_leader" style="width: 100%;" {{ $user->hasRole('Team-Leader') ? 'disabled' : '' }}>
                                    @if($user->hasRole('Team-Leader'))
                                    <option value="{{ $user->id }}" selected>{{ $user->name }}</option>
                                    @else
                                    <option selected value=""> Select Team Leader </option>
                                    @endif
                                </select>
                                @if($user->hasRole('Team-Leader'))
                                <input type="hidden" name="team_leader" value="{{ $user->id }}">
                                @endif
                                <span class="invalid-feedback" id="team_leader-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-6">
                                <label> Package </label>
                                <div class="form-group">
                                    <input type="number" class="form-control" name="package" id="package" placeholder="Package" tabindex="4"
                                        onKeyPress="return isNumberKey(event);">
                                    <span class="invalid-feedback" id="package-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <label> Reference Link</label>
                                <div class="form-group">
                                    <input type="url" class="form-control" name="referel_link" id="referel_link" placeholder="Referel Link" tabindex="7">
                                    <span class="invalid-feedback" id="referel_link-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-6">
                                <label> Project Est. Start Date <span class="text_required">*</span></label>
                                <div class="form-group">
                                    <input type="datetime-local" class="form-control" name="start_date" id="start_date"
                                        placeholder="Start Date" tabindex="5" min="<?= date('Y-m-d\T00:00'); ?>">
                                    <span class="invalid-feedback" id="start_date-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <label> Project Est. End Date <span class="text_required">*</span></label>
                                <div class="form-group">
                                    <input type="datetime-local" class="form-control" name="end_date" id="end_date"
                                        placeholder="End Date" tabindex="6" min="<?= date('Y-m-d\T00:00'); ?>">
                                    <span class="invalid-feedback" id="end_date-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 float-right">
                                <label> Project Description <span class="text_required">*</span></label>
                                <textarea class="form-control" name="description" id="description"></textarea>
                                <span class="invalid-feedback" id="description-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label class="d-flex justify-content-between align-items-center">
                                    <span>Project Documents <small class="text-muted">(Optional - PDF, Images, Docs)</small></span>
                                </label>
                                <div class="form-group custom-file-upload p-3 bg-light rounded border-dashed" style="border: 2px dashed #cbd5e1;">
                                    <input type="file" class="form-control" name="documents[]" id="project_documents" multiple>
                                    <small class="text-muted mt-1 d-block">You can select multiple files at once.</small>
                                    <span class="invalid-feedback" id="documents-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mt-3 float-roght btns_div">
                                <div class="float-right">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"> Create Project </button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Add Client Modal -->
    <div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientModalLabel">Add New Client</h5>
                    <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="frm_ajax_add_client">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label>Client/Company Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required placeholder="Enter Company Name">
                                <span class="invalid-feedback error-name"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Contact Person</label>
                                <input type="text" class="form-control" name="contact_person" placeholder="Name">
                                <span class="invalid-feedback error-contact_person"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Designation</label>
                                <input type="text" class="form-control" name="designation" placeholder="e.g. CEO, Manager">
                                <span class="invalid-feedback error-designation"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" required placeholder="email@example.com">
                                <span class="invalid-feedback error-email"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Mobile</label>
                                <input type="text" class="form-control" name="mobile" placeholder="Phone Number">
                                <span class="invalid-feedback error-mobile"></span>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>City</label>
                                <input type="text" class="form-control" name="city" placeholder="City">
                                <span class="invalid-feedback error-city"></span>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Address</label>
                                <textarea class="form-control" name="address" rows="2" placeholder="Enter Full Address"></textarea>
                                <span class="invalid-feedback error-address"></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btnmdlclose" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-save-client">Save Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {

        tinymce.init({
            selector: 'textarea#description',
            branding: false,
            plugins: [
                "advlist autolink lists link image charmap print preview anchor",
                "searchreplace visualblocks code fullscreen",
                "insertdatetime media table paste codesample"
            ],
            toolbar: "undo redo | fontselect styleselect fontsizeselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | codesample action section button",
            font_formats: "Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Book Antiqua=book antiqua,palatino; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino;",
            fontsize_formats: "8px 9px 10px 11px 12px 14px 16px 18px 20px 22px 24px 26px 28px 30px 32px 34px 36px 38px 40px 42px 44px 46px 48px 50px 52px 54px",
            height: 300
        });

        $(document).on('change', '#department', function() {
            let dept_value = $(this).val();

            // Fetch Sub Categories
            $('#category').empty().append('<option selected="selected" value="">Select Category</option>');
            $.ajax({
                type: 'GET',
                url: base_url + "/projectcategory/subcategories",
                data: {
                    'projcategory': dept_value
                },
                success: function(response) {
                    if (response.status == true) {
                        $("#category").select2({
                            data: response.data
                        });
                    }
                },
            });

            // Fetch Team Leaders
            $('#team_leader').empty().append('<option selected="selected" value="">Select Team Leader</option>');
            $.ajax({
                type: 'GET',
                url: base_url + "/projects/get-team-leaders",
                data: {
                    'category_id': dept_value
                },
                success: function(response) {
                    if (response.status == true) {
                        let data = response.data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.name
                            };
                        });
                        $("#team_leader").select2({
                            data: data
                        });

                        // Auto-select if only one TL available
                        if (data.length === 1) {
                            $('#team_leader').val(data[0].id).trigger('change');
                        }
                    }
                },
            });
        });

        // Trigger change on load to populate subcategories and TLs for default selection
        if ($('#department').val()) {
            $('#department').trigger('change');
        }

        function updateProjectName() {
            let clientText = $("#clientsid option:selected").text().trim();
            let categoryText = $("#category option:selected").text().trim();

            if (clientText && clientText !== 'Select Client' && categoryText && categoryText !== 'Select Sub Category') {
                let currentProjName = $('#project_name').val();
                if (!currentProjName || currentProjName.includes(' - ') || currentProjName === clientText || currentProjName === categoryText) {
                    $('#project_name').val(clientText + ' - ' + categoryText);
                }
            } else if (clientText && clientText !== 'Select Client') {
                let currentProjName = $('#project_name').val();
                if (!currentProjName || currentProjName.includes(' - ')) {
                    $('#project_name').val(clientText);
                }
            } else if (categoryText && categoryText !== 'Select Sub Category') {
                let currentProjName = $('#project_name').val();
                if (!currentProjName || currentProjName.includes(' - ')) {
                    $('#project_name').val(categoryText);
                }
            }
        }

        $(document).on('change', '#clientsid', updateProjectName);
        $(document).on('change', '#category', updateProjectName);

        $('#frm_create_new_project').submit(function(e) {
            e.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");

            $.ajax({
                type: 'POST',
                url: base_url + '/client/createprojecct',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".creatBtn").html('Creating...');
                    $(".creatBtn").prop('disabled', true);
                },
                success: function(response) {
                    console.log(response);
                    if (response.status == true) {
                        $('#frm_create_new_project')[0].reset();
                        alertify.success(response.message);
                        window.location.href = "{{URL::to('/projects')}}"

                    } else {
                        alertify.error(response.message);
                        $(".creatBtn").prop('disabled', false);
                        $(".creatBtn").html('Create Project');
                    }
                },
                error: function(response) {
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('Create Project');
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

        $('#frm_ajax_add_client').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = new FormData(this);
            $('.invalid-feedback').text('').hide();
            $('.form-control').removeClass('is-invalid');

            $.ajax({
                type: 'POST',
                url: '{{ route("client.ajax-create") }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".btn-save-client").html('Saving...').prop('disabled', true);
                },
                success: function(response) {
                    if (response.status) {
                        // Close modal
                        $('#addClientModal').modal('hide');

                        form[0].reset();

                        if (response.client) {
                            alertify.success(response.message);
                            var newOption = new Option(response.client.name, response.client.id, true, true);
                            $('#clientsid').append(newOption).trigger('change');
                        } else {
                            alertify.error(response.message);
                        }
                    }
                    $(".btn-save-client").html('Save Client').prop('disabled', false);
                },
                error: function(response) {
                    $(".btn-save-client").html('Save Client').prop('disabled', false);
                    if (response.status === 409 && response.responseJSON) {
                        // Client already exists and is Matured
                        alertify.error(response.responseJSON.message);
                    } else if (response.responseJSON && response.responseJSON.errors) {
                        let errors = response.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            form.find('[name="' + key + '"]').addClass('is-invalid');
                            form.find('.error-' + key).text(errors[key][0]).show();
                        });
                    } else {
                        alertify.error('An error occurred. Please try again.');
                    }
                }
            });
        });

    })
</script>

@endsection
