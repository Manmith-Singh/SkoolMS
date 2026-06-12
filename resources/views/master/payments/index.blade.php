@extends('layouts.app')
@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Payments</h3>
    <a href="{{ route('master.payments.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Record payment</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card stat-card p-3" style="border-left-color:#198754;"><div class="stat-label">Succeeded</div><div class="stat-value text-success">{{ number_format($stats['succeeded'], 2) }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3" style="border-left-color:#ffc107;"><div class="stat-label">Pending</div><div class="stat-value text-warning">{{ number_format($stats['pending'], 2) }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3" style="border-left-color:#dc3545;"><div class="stat-label">Failed</div><div class="stat-value text-danger">{{ number_format($stats['failed'], 2) }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="stat-label">Total records</div><div class="stat-value">{{ $stats['count'] }}</div></div></div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach(['pending', 'succeeded', 'failed', 'refunded'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Method</label>
                <select name="method" class="form-select">
                    <option value="">All</option>
                    @foreach(['cash', 'bank_transfer', 'cheque', 'card', 'online', 'other'] as $m)
                        <option value="{{ $m }}" @selected(request('method') === $m)>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0 datatable">
            <thead><tr><th>Date</th><th>School</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($payments as $p)
                <tr>
                    <td>{{ $p->paid_at?->format('Y-m-d H:i') ?? $p->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $p->tenant->name ?? '—' }}</td>
                    <td>@if($p->invoice)<a href="{{ route('master.invoices.show', $p->invoice) }}">{{ $p->invoice->invoice_number }}</a>@else — @endif</td>
                    <td>{{ number_format($p->amount, 2) }} {{ $p->currency }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $p->method)) }}</td>
                    <td><code>{{ $p->reference ?? '—' }}</code></td>
                    <td><span class="badge bg-{{ $p->status === 'succeeded' ? 'success' : ($p->status === 'failed' ? 'danger' : 'secondary') }}">{{ $p->status }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $payments->links() }}</div>
</div>
@endsection
