@extends('layouts.app')
@section('title', 'Fee collection report')

@section('content')
<h4 class="mb-3">Fee collection & dues</h4>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small">To</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control">
        </div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i> Apply</button></div>
    </form>
</div></div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card stat-card p-3" style="border-left-color:#198754;">
            <div class="stat-label">Collected</div>
            <div class="stat-value text-success">{{ number_format($collected, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3" style="border-left-color:#dc3545;">
            <div class="stat-label">Pending dues</div>
            <div class="stat-value text-danger">{{ number_format($pending, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3" style="border-left-color:#0d6efd;">
            <div class="stat-label">Collection rate</div>
            <div class="stat-value">
                @php $rate = ($collected + $pending) > 0 ? round(($collected / ($collected + $pending)) * 100, 1) : 0; @endphp
                {{ $rate }}%
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white"><strong>Collection by category</strong></div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Category</th><th class="text-end">Collected</th></tr></thead>
            <tbody>
            @forelse($byCategory as $name => $amount)
                <tr>
                    <td>{{ $name }}</td>
                    <td class="text-end text-success">{{ number_format($amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-center text-muted py-3">No payments in this period.</td></tr>
            @endforelse
            </tbody>
            <tfoot>
                <tr class="table-active">
                    <th>Total</th>
                    <th class="text-end">{{ number_format($byCategory->sum(), 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
