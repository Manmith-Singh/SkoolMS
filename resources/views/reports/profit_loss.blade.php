@extends('layouts.app')
@section('title', 'Profit & Loss')

@section('content')
<h4 class="mb-3">Profit & Loss</h4>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label small">Period</label>
            <select name="period" class="form-select">
                <option value="daily" @selected(request('period', 'monthly') == 'daily')>Daily</option>
                <option value="weekly" @selected(request('period') == 'weekly')>Weekly</option>
                <option value="monthly" @selected(request('period', 'monthly') == 'monthly')>Monthly</option>
                <option value="yearly" @selected(request('period') == 'yearly')>Yearly</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Date</label>
            <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="form-control">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-outline-primary"><i class="fas fa-filter"></i> Apply</button>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'xlsx']) }}" class="btn btn-outline-success"><i class="fas fa-file-excel"></i> XLSX</a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-outline-danger"><i class="fas fa-file-pdf"></i> PDF</a>
        </div>
    </form>
</div></div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card stat-card p-3" style="border-left-color:#198754;">
            <div class="stat-label">Total income</div>
            <div class="stat-value text-success">{{ number_format($totalIncome, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3" style="border-left-color:#dc3545;">
            <div class="stat-label">Total expenditure</div>
            <div class="stat-value text-danger">{{ number_format($totalExpenditure, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3" style="border-left-color:{{ $net >= 0 ? '#0d6efd' : '#dc3545' }};">
            <div class="stat-label">Net {{ $net >= 0 ? 'profit' : 'loss' }}</div>
            <div class="stat-value" style="color:{{ $net >= 0 ? '#0d6efd' : '#dc3545' }}; font-weight:700;">
                {{ number_format($net, 2) }}
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-success text-white"><strong>Income by type</strong></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Category</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        @forelse($incomeByType as $type)
                        <tr>
                            <td>{{ $type->name ?? 'Uncategorized' }}</td>
                            <td class="text-end text-success">{{ number_format($type->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">No income.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-active">
                            <th>Total</th>
                            <th class="text-end text-success">{{ number_format(collect($incomeByType)->sum('total'), 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-danger text-white"><strong>Expenditure by type</strong></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Category</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        @forelse($expenditureByType as $type)
                        <tr>
                            <td>{{ $type->name ?? 'Uncategorized' }}</td>
                            <td class="text-end text-danger">{{ number_format($type->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">No expenditure.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-active">
                            <th>Total</th>
                            <th class="text-end text-danger">{{ number_format(collect($expenditureByType)->sum('total'), 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
