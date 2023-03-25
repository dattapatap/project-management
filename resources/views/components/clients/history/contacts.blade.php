@extends('layouts.app')

@section('content')

<style>
    .table td, .table th {
        padding: 0.5rem;
        vertical-align: top;
        border-top: 0px solid #eff2f7;
    }
    td:nth-child(1), td:nth-child(3)  { font-weight: 600; }
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
                        <a class="nav-link active" href="javascript:void(0);" >
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
                        <a class="nav-link" href="{{ url('clients/'.base64_encode($client->id).'/'.'docs' ) }}" role="tab">
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

                <!-- Tab contact panes -->
                <div class="tab-content p-3">
                    <div class="tab-pane active" id="contacts" role="tabpanel">
                        <h4 class="lbl-heading-pane fs-16"> Company Details</h4>
                        <hr>
                        <table width="100%" class="table">
                            <tr>
                                <td> Company Name:</td>
                                <td> {{ $client->name }} </td>
                                <td> Customer Type:</td>
                                <td> {{ $client->category }} </td>
                            </tr>

                            <tr>
                                <td> Address 1:</td>
                                <td width="35%"> {{ $client->address }} </td>
                                <td> Address 2:</td>
                                <td width="35%">  {{ $client->alt_address }} </td>
                            </tr>
                            <tr>
                                <td> City:</td>
                                <td> {{ $client->city }} </td>
                                <td> Website Link:</td>
                                <td>
                                    @isset($client->website_link)
                                        <a href="{{ $client->website_link }}" target="_new"> {{ $client->website_link }} </a>
                                    @endisset
                                </td>
                            </tr>

                            <tr>
                                <td> Email:</td>
                                <td> {{ $client->email }} </td>
                                <td> Alternate Email:</td>
                                <td> {{ $client->alt_email }} </td>
                            </tr>

                            <tr>
                                <td> Mobile No:</td>
                                <td> {{ $client->mobile }} </td>
                                <td> Alternate Mobile:</td>
                                <td> {{ $client->alt_mobile }} </td>
                            </tr>

                            <tr>
                                <td> Telephone No:</td>
                                <td> {{ $client->telephone }} </td>
                                <td> Alternate Telephone No:</td>
                                <td> {{ $client->alt_telephone }} </td>
                            </tr>

                            <tr>
                                <td> Contact Person:</td>
                                <td> {{ $client->cont_person }} </td>
                                <td> Designation:</td>
                                <td> {{ $client->designation }} </td>
                            </tr>
                            <tr>
                                <td> Created: </td>
                                <td> {{ Carbon\Carbon::parse($client->created_at)->format('d-M-Y') }} </td>
                                <td> Updated: </td>
                                <td> {{ Carbon\Carbon::parse($client->updated_at)->format('d-M-Y') }} </td>
                            </tr>
                        </table>

                        <hr>
                        <h4 class="lbl-heading-pane fs-16"> Referral Details</h4>
                        <hr>
                        <table width="100%">
                            <tr>
                                <td> Sales Executive:</td>
                                <td > {{ $client->referral->name }} ( {{ $client->referral->mobile }} )</td>
                                <td> Tele/CC Executive:</td>
                                <td > {{ $client->telereferral->name }} ( {{ $client->telereferral->mobile }} )</td>
                            </tr>
                        </table>

                    </div>
                </div>

            </div>
        </div>

    </div>
    <!-- end row -->
</div>

@include('components.clients.history.visitingcard')

@endsection
@section('scripts')
<script>
    $(document).ready(function(){
    })
</script>
@endsection
