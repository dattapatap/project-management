@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Batches</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Batch</li>
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
                    <div class="header-title searchdiv mb-4">

                        <form class="form-inline" method="GET" action="{{ route('batches.filterbatches') }}">
                            <div class="input-group mt-3 mt-sm-0 mr-sm-3">
                                <input type="text" id="filter" class="form-control" name="batch-filter" style="width: 270px;"
                                    placeholder="Product/Batch/GTIN/SSCC/Item code" value="{{ $filter }}">
                                <div class="input-group-prepend">
                                    <button type="submit" class="btn btn-primary mb-2">Filter</button>
                                </div>
                            </div>
                        </form>

                        <div class="btn-group mr-1 mt-1 mb-2 float-right">



                            <a href="{{ route('batches.create')}}" type="button" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i>
                                New Batch
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered mb-0 table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="text-bold" style="width: 40px;">Sl No</th>
                                    <th scope="col" class="text-center">Date</th>
                                    <th scope="col">Batch No.</th>
                                    <th scope="col" width="20%">Product</th>
                                    <th class="text-center" scope="col">SSCC Code</th>
                                    <th scope="col">Batch Size</th>
                                    <th scope="col">Drum No.</th>
                                    <th scope="col">Tot Drums</th>
                                    {{-- <th scope="col">Item Code</th> --}}
                                    <th scope="col" class="text-center">Labels</th>
                                    <th class="text-center" scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($batches as $key=>$items)
                                <tr>
                                    <td> {{  ($batches->currentpage()-1) * $batches->perpage() + $key + 1 }} </td>
                                    <td> {{  Carbon\Carbon::parse($items->created_at)->format('d-m-Y') }} </td>
                                    <td>
                                        <a href="{{ route('batches.batchlist', str_replace('/', '__', $items->batch_no_detail )  ) }}" style="color: #06a1ff;">
                                            {{ $items->batch_no_detail }}
                                        </a>
                                    </td>
                                    <td width="20%">{{ $items->product->product_name }} </td>
                                    <td>{{ $items->sscc_code }} </td>
                                    <td class="text-center">{{ $items->batch_size }} </td>
                                    <td class="text-center">{{ $items->drum_no }} </td>
                                    <td class="text-center">{{ $items->tot_drums }} </td>
                                    {{-- <td>{{ $items->item_code }} </td> --}}

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

                                        @if($user->role == "Admin")
                                            <form method="post" action="{{ route('batches.destroy',[$items->id]) }}"  style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"  onclick="return confirm('Do you want to delete this item?')"
                                                    data-toggle="tooltip" data-placement="bottom" title="Delete Batch"><i class="mdi mdi-delete-outline"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>


                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">
                                        No Batches exist in database.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="row mt-3 float-right">
                        {{ $batches->links("pagination::bootstrap-4") }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
</div>
@endsection
