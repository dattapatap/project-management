@extends('layouts.app')

@section('content')

@php
    $user  = Auth::user();
@endphp
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Products</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Products</li>
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

                        <form class="form-inline" method="GET" action="{{ route('products.filterproduct') }}">
                            <div class="input-group mt-3 mt-sm-0 mr-sm-3">
                                <input type="text" id="filter" class="form-control" name="product-filter" style="width: 250px;"
                                    placeholder="Product/Brand/GTIN" value="{{ $filter }}" required>
                                <div class="input-group-prepend">
                                    <button type="submit" class="btn btn-primary mb-2">Filter</button>
                                </div>
                            </div>

                        </form>

                        <div class="btn-group mr-1 mt-1 mb-2 float-right">
                            <button type="button" class="btn btn-primary dropdown-toggle"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Action  <i class="mdi mdi-chevron-down"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('products.create') }}">Add Product</a>
                                <a class="dropdown-item" href="{{ route('products.upload') }}">Upload Product</a>
                            </div>
                        </div><!-- /btn-group -->
                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered mb-0 table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" style="width: 60px;">Sl No</th>
                                    <th scope="col">Brand Name</th>
                                    <th scope="col" width="45%">Product Name</th>
                                    <th scope="col">GTIN Code</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $key=>$items)
                                <tr>
                                    <td>{{  ($products->currentpage()-1) * $products->perpage() + $key + 1 }} </td>
                                    <td> {{ $items->brand->brand_name }} </td>
                                    <td width="45%">{{ $items->product_name }} </td>
                                    <td>{{ $items->gtin_no }}   </td>                                    <td>
                                        @if($items->status == true)
                                            <a href="{{ route('products.changeStatus', $items->id ) }}" class="btn btn-success btn-sm">Active</a>
                                        @else
                                            <a href="{{ route('products.changeStatus', $items->id ) }}" class="btn btn-danger btn-sm">In-Active</a>
                                        @endif

                                    </td>
                                    <td>
                                        <a type="button" class="btn btn-outline-success btn-sm"
                                            href="{{ route('products.edit', $items->id ) }}">
                                            Edit
                                        </a>
                                        @if($user->role == "Admin")
                                            <form method="post" action="{{ route('products.destroy',[$items->id]) }}"  style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <input type="submit" value="Delete" class="btn btn-outline-danger btn-sm">
                                            </form>
                                        @endif

                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">
                                        No Product exist in database.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="row mt-3 float-right">
                        {{ $products->links("pagination::bootstrap-4") }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
</div>
@endsection
