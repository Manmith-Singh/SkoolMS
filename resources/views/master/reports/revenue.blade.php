@extends('layouts.app')
@section('title', 'Revenue')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Revenue</h3>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card stat-card p-3" style="border-left-color:#198754;"><div class="stat-label">All time</div><div class="stat-value text-success">{{ number_format($totals['all'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card stat-card p-3"><div class="stat-label">Last 12 months</div><div class="stat-value">{{ number_format($totals['year'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card stat-card p-3"><div class="stat-label">Last 30 days</div><div class="stat-value">{{ number_format($totals['month'], 2) }}</div></div></div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Date</th><th>School</th><th>Amount</th><th>Method</th><th>Reference</th></tr></thead>
            <tbody>
            @foreach($rows as $p)
                <tr>
                    <td>{{ $p->paid_at?->format('Y-m-d H:i') ?? $p->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $p->tenant->name ?? '—' }}</td>
                    <td>{{ number_format($p->amount, 2) }} {{ $p->currency }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $p->method)) }}</td>
                    <td><code>{{ $p->reference ?? '—' }}</code></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $rows->links() }}</div>
</div>
@endsection
