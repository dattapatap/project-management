@extends('layouts.app')

@section('content')
<div class="container-fluid erp-page erp-page--csd">
    @include('layouts.partials.erp-page-header', [
        'title' => 'Edit Contract',
        'subtitle' => $contract->client->name ?? 'AMC / Support Contract',
        'actions' => '<a href="' . route('csd.amc.index') . '" class="btn btn-light btn-sm"><i class="mdi mdi-arrow-left"></i> Back to list</a>',
    ])

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('csd.amc.update', $contract) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('components.csd.amc.partials.form', ['contract' => $contract, 'clients' => $clients])
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save-outline"></i> Update Contract</button>
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
        var cycle = $('#billingCycle').val();
        $('#amountPeriodLabel').text(cycle === 'monthly' ? 'month' : 'year');
        var start = $('#contractStartDate').val();
        if (start && $('#contractEndDate').data('manual') !== true) {
            $('#contractEndDate').val(addPeriod(start, cycle));
        }
    }
    $('#billingCycle, #contractStartDate').on('change', syncEndDate);
    $('#contractEndDate').on('change', function () { $(this).data('manual', true); });
    $('#contractEndDate').data('manual', true);
    syncEndDate();
})();
</script>
@endsection
