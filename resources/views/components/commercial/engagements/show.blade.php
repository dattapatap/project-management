@extends('layouts.app')

@section('content')
<div class="container-fluid erp-page pb-4">
    @include('layouts.partials.erp-page-header', [
        'title' => $engagement->engagement_no,
        'subtitle' => $engagement->clients?->name . ' — ' . ucfirst(str_replace('_', ' ', $engagement->engagement_type)),
    ])

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Status</h6>
                    <h4>{{ $engagement->statusLabel() }}</h4>
                    <hr>
                    <p class="mb-1"><strong>Parent order:</strong> {{ $engagement->parent?->engagement_no ?? 'Root' }}</p>
                    <p class="mb-1"><strong>Est. value:</strong> {{ $engagement->estimated_value ? '₹ '.number_format($engagement->estimated_value, 2) : '—' }}</p>
                    <p class="mb-1"><strong>Closed value:</strong> {{ $engagement->closed_value ? '₹ '.number_format($engagement->closed_value, 2) : '—' }}</p>
                    <p class="mb-1"><strong>NSD owner:</strong> {{ $engagement->salesOwner?->name ?? '—' }}</p>
                    <p class="mb-1"><strong>CSD owner:</strong> {{ $engagement->csdOwner?->name ?? '—' }}</p>
                    <p class="mb-1"><strong>CSD Team Leader:</strong> {{ $engagement->csdTeamLeader?->name ?? '—' }}</p>
                    @if($engagement->project_id)
                    <p class="mb-0"><strong>OD project:</strong> <a href="{{ url('projects') }}">#{{ $engagement->project_id }}</a></p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Client engagement chain</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Order</th><th>Type</th><th>Title</th><th>Status</th><th>Project</th></tr></thead>
                            <tbody>
                                @foreach($timeline as $item)
                                <tr class="{{ $item->id === $engagement->id ? 'table-active' : '' }}">
                                    <td>{{ $item->engagement_no }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $item->engagement_type)) }}</td>
                                    <td>{{ $item->title }}</td>
                                    <td>{{ $item->statusLabel() }}</td>
                                    <td>{{ $item->project_id ? '#'.$item->project_id : '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Audit trail</h5>
                    @forelse($engagement->events as $event)
                    <div class="border-bottom py-2">
                        <strong>{{ str_replace('_', ' ', ucfirst($event->event_type)) }}</strong>
                        @if($event->from_status) <span class="text-muted">{{ $event->from_status }} → {{ $event->to_status }}</span> @endif
                        <br><small class="text-muted">{{ $event->notes }}</small>
                        <br><small>{{ $event->created_at->format('d M Y H:i') }} · {{ $event->creator?->name }}</small>
                    </div>
                    @empty
                    <p class="text-muted mb-0">No events yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
