@extends('layouts.app')
@section('title', $invoice->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h3 class="mb-0">Invoice {{ $invoice->invoice_number }}
            <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'secondary') }} ms-2">{{ $invoice->status }}</span>
        </h3>
        <small class="text-muted">For {{ $invoice->tenant->name }} ({{ $invoice->tenant->subdomain }})</small>
    </div>
    <div class="btn-group">
        <form method="POST" action="{{ route('master.invoices.mark-paid', $invoice) }}">
            @csrf
            <button class="btn btn-outline-success" @disabled($invoice->status === 'paid')><i class="fas fa-check me-1"></i>Mark paid</button>
        </form>
        <a href="{{ route('master.invoices.index') }}" class="btn btn-link">Back</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card stat-card p-3"><div class="stat-label">Subtotal</div><div class="stat-value">{{ number_format($invoice->subtotal, 2) }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="stat-label">Tax</div><div class="stat-value">{{ number_format($invoice->tax, 2) }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3" style="border-left-color:#198754;"><div class="stat-label">Total</div><div class="stat-value text-success">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3" style="border-left-color:#0d6efd;"><div class="stat-label">Paid</div><div class="stat-value">{{ number_format($invoice->amountPaid(), 2) }}</div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header bg-white"><strong>Line items</strong></div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Description</th><th>Qty</th><th>Unit price</th><th>Amount</th></tr></thead>
            <tbody>
            @foreach($invoice->items as $it)
                <tr>
                    <td>{{ $it->description }}</td>
                    <td>{{ $it->quantity }}</td>
                    <td>{{ number_format($it->unit_price, 2) }}</td>
                    <td>{{ number_format($it->amount, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white"><strong>Payments</strong></div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th><th>Paid at</th></tr></thead>
            <tbody>
            @forelse($invoice->payments as $p)
                <tr>
                    <td>{{ number_format($p->amount, 2) }} {{ $p->currency }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $p->method)) }}</td>
                    <td><code>{{ $p->reference ?? '—' }}</code></td>
                    <td><span class="badge bg-{{ $p->status === 'succeeded' ? 'success' : 'secondary' }}">{{ $p->status }}</span></td>
                    <td>{{ $p->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No payments recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
