@extends('layouts.app')

@section('content')


<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18" style="color: #3d8ef8;">Batch No - {{ $batchno  }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/batches') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Batch List</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="header-title mb-4">
                        <div class="btn-group mr-1 mt-1 mb-2 float-right text-roght ">
                            @if($batches->count() > 0)
                            <a href="{{ route('batches.mearge', str_replace('/', '__', $batchno ) )}}" type="button" class="btn btn-primary btn-sm float-right">
                                <i class="mdi mdi-download"></i>
                                Download Labels
                            </a>
                            @endif
                        </div>

                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered mb-0 table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="text-bold" style="width: 60px;">Sl No</th>
                                    <th scope="col" class="text-center">Date</th>
                                    <th scope="col">Batch No.</th>
                                    <th scope="col" width="20%">Product</th>
                                    <th class="text-center" scope="col">SSCC Code</th>
                                    <th scope="col">Batch Size</th>
                                    <th scope="col">Drum No.</th>
                                    <th scope="col">Tot Drums</th>
                                    <th scope="col" class="text-center">Labels</th>
                                    <th class="text-center" scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($batches as $key=>$items)
                                <tr>
                                    <td> {{  $loop->index +1  }} </td>
                                    <td> {{  Carbon\Carbon::parse($items->created_at)->format('d-m-Y') }} </td>
                                    <td>
                                        <a href="{{ route('batches.show', $items->id ) }}" style="color: #06a1ff;">
                                            {{ $items->batch_no_detail }}
                                        </a>
                                    </td>
                                    <td width="20%">{{ $items->product->product_name }} </td>
                                    <td>{{ $items->sscc_code }} </td>
                                    <td class="text-center">{{ $items->batch_size }} </td>
                                    <td class="text-center">{{ $items->drum_no }} </td>
                                    <td class="text-center">{{ $items->tot_drums }} </td>
                                    <td class="text-center">
                                        <a type="button" class="btn btn-outline-success btn-sm" href="{{ route('batches.downloadAck', $items->id ) }}"
                                            data-toggle="tooltip" data-placement="bottom" title="Download QR Code">
                                            <i class="mdi mdi-qrcode"></i>
                                        </a>
                                        <a type="button" class="btn btn-outline-success btn-sm" href="{{ route('batches.downloadLbl', $items->id ) }}"
                                            data-toggle="tooltip" data-placement="bottom" title="Download Label">
                                            <i class="mdi mdi-label"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a type="button" class="btn btn-outline-warning btn-sm"
                                                href="{{ route('batches.edit', $items->id ) }}"
                                                data-toggle="tooltip" data-placement="bottom" title="Edit Batch">
                                                <i class="mdi mdi-square-edit-outline"></i>
                                        </a>
                                        <a type="button" class="btn btn-outline-success btn-sm" target="_bland"
                                                href="{{ route('batches.show', $items->id ) }}" data-toggle="tooltip" data-placement="bottom" title="View Batch">
                                                <i class="mdi mdi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty

                                    <tr>
                                        <td colspan="10" class="text-danger text-center">
                                            Invalid Batch Code
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
</div>
@endsection
