@extends('layouts.app')
@section('title', 'Expenditure')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Expenditure</h4>
    <a href="{{ route('expenditure.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Record expenditure</a>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="card-title mb-0">Total expenditure</h6>
                <h3 class="mb-0">{{ number_format($total, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="row g-2">
        <div class="col-md-3">
            <select name="expenditure_type_id" class="form-select">
                <option value="">All types</option>
                @foreach($types as $t)
                    <option value="{{ $t->id }}" @selected(request('expenditure_type_id') == $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="from" value="{{ request('from') }}" class="form-control" placeholder="From date">
        </div>
        <div class="col-md-3">
            <input type="date" name="to" value="{{ request('to') }}" class="form-control" placeholder="To date">
        </div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i> Filter</button></div>
    </form>
</div></div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr><th>Date</th><th>Type</th><th>Description</th><th>Amount</th><th>Reference</th><th>Paid by</th><th>Approved by</th><th class="no-sort">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($transactions as $e)
                <tr>
                    <td>{{ $e->date->format('d M Y') }}</td>
                    <td>{{ $e->type->name ?? '—' }}</td>
                    <td>{{ $e->description ?? '—' }}</td>
                    <td>{{ number_format($e->amount, 2) }}</td>
                    <td>{{ $e->reference ?? '—' }}</td>
                    <td>{{ $e->paid_by ?? '—' }}</td>
                    <td>{{ $e->approved_by ?? '—' }}</td>
                    <td>
                        <a href="{{ route('expenditure.edit', $e) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('expenditure.destroy', $e) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <span><strong>Total: {{ number_format($total, 2) }}</strong></span>
        {{ $transactions->links() }}
    </div>
</div>
@endsection
