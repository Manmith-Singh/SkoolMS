@extends('layouts.app')
@section('title', 'Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Invoices</h3>
    <a href="{{ route('master.invoices.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New invoice</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card stat-card p-3"><div class="stat-label">Total billed</div><div class="stat-value">{{ number_format($totals['total'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card stat-card p-3" style="border-left-color:#198754;"><div class="stat-label">Paid</div><div class="stat-value text-success">{{ number_format($totals['paid'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card stat-card p-3" style="border-left-color:#dc3545;"><div class="stat-label">Overdue</div><div class="stat-value text-danger">{{ number_format($totals['overdue'], 2) }}</div></div></div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="invoice # or school name">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach (['draft', 'sent', 'paid', 'overdue', 'cancelled', 'refunded'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
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
            <thead>
                <tr><th>#</th><th>School</th><th>Total</th><th>Status</th><th>Issued</th><th>Due</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($invoices as $i)
                <tr>
                    <td><a href="{{ route('master.invoices.show', $i) }}">{{ $i->invoice_number }}</a></td>
                    <td>{{ $i->tenant->name ?? '—' }}</td>
                    <td>{{ number_format($i->total, 2) }} {{ $i->currency }}</td>
                    <td><span class="badge bg-{{ $i->status === 'paid' ? 'success' : ($i->status === 'overdue' ? 'danger' : 'secondary') }}">{{ $i->status }}</span></td>
                    <td>{{ $i->issue_date->format('Y-m-d') }}</td>
                    <td>{{ $i->due_date->format('Y-m-d') }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('master.invoices.mark-paid', $i) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-success" @disabled($i->status === 'paid')><i class="fas fa-check"></i></button>
                        </form>
                        <form method="POST" action="{{ route('master.invoices.destroy', $i) }}" class="d-inline" onsubmit="return confirm('Delete invoice?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $invoices->links() }}</div>
</div>
@endsection
