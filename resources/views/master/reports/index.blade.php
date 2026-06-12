@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<h3 class="mb-4">Reports</h3>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card stat-card p-3"><div class="stat-label">Tenants</div><div class="stat-value">{{ $stats['tenants'] }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3" style="border-left-color:#198754;"><div class="stat-label">Active</div><div class="stat-value text-success">{{ $stats['active_tenants'] }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="stat-label">Users</div><div class="stat-value">{{ $stats['users'] }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3" style="border-left-color:#198754;"><div class="stat-label">MRR (USD)</div><div class="stat-value text-success">{{ number_format($stats['mrr'], 2) }}</div></div></div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white"><strong>Invoices & revenue (last 12 months)</strong></div>
            <div class="card-body">
                <canvas id="revChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Top tenants by user count</strong>
                <a href="{{ route('master.tenants.index') }}" class="btn btn-sm btn-outline-primary">All tenants</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>School</th><th>Subdomain</th><th>Users</th></tr></thead>
                    <tbody>
                    @foreach($tenantUsage as $t)
                        <tr>
                            <td>{{ $t->name }}</td>
                            <td><code>{{ $t->subdomain }}</code></td>
                            <td>{{ $t->users_count }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Activity log (recent)</strong>
        <a href="{{ route('master.audit.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
    </div>
    <div class="card-body">
        <p class="text-muted">See the <a href="{{ route('master.audit.index') }}">audit log</a> for a full searchable history of administrative actions.</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
const labels = @json(collect($months)->pluck('label'));
const invoices = @json(collect($months)->pluck('invoices'));
const revenue = @json(collect($months)->pluck('revenue'));

new Chart(document.getElementById('revChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'Invoices',
                data: invoices,
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                yAxisID: 'y',
            },
            {
                label: 'Revenue',
                data: revenue,
                type: 'line',
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.2)',
                tension: 0.3,
                yAxisID: 'y1',
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y:  { type: 'linear', position: 'left', title: { display: true, text: 'Invoices' } },
            y1: { type: 'linear', position: 'right', title: { display: true, text: 'Revenue' }, grid: { drawOnChartArea: false } }
        }
    }
});
</script>
@endpush
