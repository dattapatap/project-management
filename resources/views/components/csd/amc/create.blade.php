@extends('layouts.app')

@section('content')
<div class="container-fluid erp-page erp-page--csd">
    @include('layouts.partials.erp-page-header', [
        'title' => 'Add AMC / Support Contract',
        'subtitle' => 'Record monthly or yearly maintenance agreement with optional document.',
        'actions' => '<a href="' . route('csd.amc.index') . '" class="btn btn-light btn-sm"><i class="mdi mdi-arrow-left"></i> Back to list</a>',
    ])

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('csd.amc.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('components.csd.amc.partials.form', ['contract' => null, 'clients' => $clients])
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save-outline"></i> Save Contract</button>
            <a href="{{ route('csd.amc.index') }}" class="btn btn-light ml-1">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
(function () {
    function addPeriod(start, cycle) {
        if (!start) return '';
        var d = new Date(start + 'T00:00:00');
        if (cycle === 'monthly') { d.setMonth(d.getMonth() + 1); } else { d.setFullYear(d.getFullYear() + 1); }
        d.setDate(d.getDate() - 1);
        return d.toISOString().slice(0, 10);
    }
    function syncEndDate() {
        var start = $('#contractStartDate').val();
        var cycle = $('#billingCycle').val();
        if (start && $('#contractEndDate').data('manual') !== true) {
            $('#contractEndDate').val(addPeriod(start, cycle));
        }
        $('#amountPeriodLabel').text(cycle === 'monthly' ? 'month' : 'year');
    }
    $('#billingCycle, #contractStartDate').on('change', syncEndDate);
    $('#contractEndDate').on('change', function () { $(this).data('manual', true); });
    syncEndDate();
})();
</script>
@endsection
