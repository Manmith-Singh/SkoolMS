@extends('layouts.app')
@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h4 class="mb-0">Payments</h4>
        <small class="text-muted">Total collected: <strong>{{ number_format($totalCollected, 2) }}</strong></small>
    </div>
    <a href="{{ route('fees.payments.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Record payment</a>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="row g-2">
        <div class="col-md-3"><input type="date" name="from" value="{{ request('from') }}" class="form-control" placeholder="From"></div>
        <div class="col-md-3"><input type="date" name="to"   value="{{ request('to') }}"   class="form-control" placeholder="To"></div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i> Filter</button></div>
    </form>
</div></div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr><th>Receipt</th><th>Date</th><th>Student</th><th>Class</th><th>Category</th><th>Amount</th><th>Mode</th><th>By</th><th class="no-sort">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($payments as $p)
                <tr>
                    <td><a href="{{ route('fees.payments.receipt', $p) }}"><code>{{ $p->receipt_no }}</code></a></td>
                    <td>{{ $p->payment_date->format('d M Y') }}</td>
                    <td>{{ $p->student->full_name ?? '—' }}</td>
                    <td>{{ $p->student->schoolClass->display_name ?? '—' }}</td>
                    <td>{{ $p->fee->category->name ?? '—' }}</td>
                    <td>{{ number_format($p->amount_paid, 2) }}</td>
                    <td><span class="badge bg-info">{{ ucfirst(str_replace('_',' ', $p->mode)) }}</span></td>
                    <td>{{ $p->received_by ?? '—' }}</td>
                    <td>
                        <a href="{{ route('fees.payments.receipt', $p) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-print"></i></a>
                        <form method="POST" action="{{ route('fees.payments.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Reverse this payment?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-undo"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $payments->links() }}</div>
</div>
@endsection
