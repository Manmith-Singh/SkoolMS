@extends('layouts.app')
@section('title', 'Expenditure report')

@section('content')
<h4 class="mb-3">Expenditure report</h4>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label small">Period</label>
            <select name="period" class="form-select" onchange="this.form.submit()">
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
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-outline-primary"><i class="fas fa-filter"></i> Apply</button>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'xlsx']) }}" class="btn btn-outline-success"><i class="fas fa-file-excel"></i> XLSX</a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-outline-danger"><i class="fas fa-file-pdf"></i> PDF</a>
        </div>
    </form>
</div></div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card stat-card p-3" style="border-left-color:#dc3545;">
            <div class="stat-label">Total expenditure</div>
            <div class="stat-value text-danger">{{ number_format($total, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3" style="border-left-color:#0d6efd;">
            <div class="stat-label">Transaction count</div>
            <div class="stat-value">{{ count($transactions) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3" style="border-left-color:#6f42c1;">
            <div class="stat-label">{{ ucfirst($period) }}</div>
            <div class="stat-value">@php
                $s = \Carbon\Carbon::parse($start);
                $e = \Carbon\Carbon::parse($end);
            @endphp
            @switch($period)
                @case('daily')  {{ $s->format('d M Y') }} @break
                @case('weekly') {{ $s->format('d M') }} — {{ $e->format('d M Y') }} @break
                @case('monthly'){{ $s->format('M Y') }} @break
                @case('yearly') {{ $s->format('Y') }} @break
                @default        {{ $s->format('d M Y') }}
            @endswitch</div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr><th>Date</th><th>Type</th><th>Description</th><th class="text-end">Amount</th><th>Reference</th><th>Paid by</th><th>Approved by</th></tr>
            </thead>
            <tbody>
                @foreach($transactions as $t)
                <tr>
                    <td>{{ $t->date?->format('d M Y') ?? '—' }}</td>
                    <td>{{ $t->expenditureType->name ?? '—' }}</td>
                    <td>{{ $t->description ?? '—' }}</td>
                    <td class="text-end">{{ number_format($t->amount, 2) }}</td>
                    <td>{{ $t->reference ?? '—' }}</td>
                    <td>{{ $t->paid_by ?? '—' }}</td>
                    <td>{{ $t->approved_by ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white"><strong>Breakdown by type</strong></div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Type</th><th class="text-end">Amount</th></tr></thead>
            <tbody>
                @forelse($byType as $name => $amount)
                <tr>
                    <td>{{ $name }}</td>
                    <td class="text-end">{{ number_format($amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="2" class="text-center text-muted py-3">No data.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="table-active">
                    <th>Total</th>
                    <th class="text-end">{{ number_format($byType->sum(), 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
