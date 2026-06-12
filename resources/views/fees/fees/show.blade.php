@extends('layouts.app')
@section('title', 'Fee #' . $fee->id)

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h4 class="mb-0">{{ $fee->student->full_name }} — {{ $fee->category->name }}</h4>
        <small class="text-muted">Due {{ $fee->due_date->format('d M Y') }} · Status <span class="badge badge-status-{{ $fee->status }}">{{ $fee->status }}</span></small>
    </div>
    <a href="{{ route('fees.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="text-muted small text-uppercase">Summary</h6>
            <table class="table table-sm mb-0">
                <tr><th>Amount</th><td>{{ number_format($fee->amount, 2) }}</td></tr>
                <tr><th>Paid</th><td class="text-success">{{ number_format($fee->paid_amount, 2) }}</td></tr>
                <tr><th>Balance</th><td class="text-danger">{{ number_format($fee->balance(), 2) }}</td></tr>
            </table>
            @if(!$fee->isFullyPaid())
                <a href="{{ route('fees.payments.create', ['fee_id' => $fee->id]) }}" class="btn btn-success w-100 mt-3">
                    <i class="fas fa-cash-register me-1"></i> Record payment
                </a>
            @endif
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">Payments</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Receipt</th><th>Date</th><th>Amount</th><th>Mode</th><th>Ref</th><th>Received by</th></tr></thead>
                    <tbody>
                    @forelse($fee->payments as $p)
                        <tr>
                            <td><a href="{{ route('fees.payments.receipt', $p) }}"><code>{{ $p->receipt_no }}</code></a></td>
                            <td>{{ $p->payment_date->format('d M Y') }}</td>
                            <td>{{ number_format($p->amount_paid, 2) }}</td>
                            <td>{{ ucfirst(str_replace('_',' ', $p->mode)) }}</td>
                            <td>{{ $p->transaction_ref ?? '—' }}</td>
                            <td>{{ $p->received_by ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No payments yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
