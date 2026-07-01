@extends('layouts.app')
@section('title', 'Income')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Income</h4>
    <a href="{{ route('income.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Record income</a>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title mb-0">Total income</h6>
                <h3 class="mb-0">{{ number_format($total, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="row g-2">
        <div class="col-md-3">
            <select name="income_type_id" class="form-select">
                <option value="">All types</option>
                @foreach($types as $t)
                    <option value="{{ $t->id }}" @selected(request('income_type_id') == $t->id)>{{ $t->name }}</option>
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
                <tr><th>Date</th><th>Type</th><th>Description</th><th>Amount</th><th>Reference</th><th>Received by</th><th class="no-sort">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($transactions as $i)
                <tr>
                    <td>{{ $i->date->format('d M Y') }}</td>
                    <td>{{ $i->type->name ?? '—' }}</td>
                    <td>{{ $i->description ?? '—' }}</td>
                    <td>{{ number_format($i->amount, 2) }}</td>
                    <td>{{ $i->reference ?? '—' }}</td>
                    <td>{{ $i->received_by ?? '—' }}</td>
                    <td>
                        <a href="{{ route('income.edit', $i) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('income.destroy', $i) }}" class="d-inline" onsubmit="return confirm('Delete?')">
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
