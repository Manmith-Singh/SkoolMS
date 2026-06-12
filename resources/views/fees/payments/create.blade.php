@extends('layouts.app')
@section('title', 'Record payment')

@section('content')
<h4 class="mb-3">Record payment</h4>

@if($fee)
    <div class="alert alert-info">
        Recording payment for <strong>{{ $fee->student->full_name }}</strong> — {{ $fee->category->name }}.
        Outstanding: <strong>{{ number_format($fee->balance(), 2) }}</strong>.
    </div>
@endif

<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('fees.payments.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Fee *</label>
                <select name="fee_id" class="form-select" required>
                    <option value="">—</option>
                    @if($fee)
                        <option value="{{ $fee->id }}" selected>
                            {{ $fee->student->full_name }} — {{ $fee->category->name }} ({{ number_format($fee->balance(), 2) }} due)
                        </option>
                    @else
                        @foreach(\App\Models\Tenant\Fee::with(['student','category'])->whereIn('status', ['pending','partial','overdue'])->orderBy('id')->limit(500)->get() as $opt)
                            <option value="{{ $opt->id }}" @selected(old('fee_id') == $opt->id)>
                                {{ $opt->student->full_name }} — {{ $opt->category->name }} ({{ number_format($opt->balance(), 2) }} due)
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Amount *</label>
                <input type="number" step="0.01" min="0.01" name="amount_paid" value="{{ old('amount_paid', $fee?->balance()) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment date *</label>
                <input type="date" name="payment_date" value="{{ old('payment_date', today()->toDateString()) }}" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Mode *</label>
                <select name="mode" class="form-select" required>
                    @foreach(['cash','cheque','bank_transfer','card','online','other'] as $m)
                        <option value="{{ $m }}" @selected(old('mode', 'cash') === $m)>{{ ucfirst(str_replace('_',' ', $m)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Transaction ref</label>
                <input type="text" name="transaction_ref" value="{{ old('transaction_ref') }}" class="form-control" placeholder="Cheque no / txn id">
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-success"><i class="fas fa-save me-1"></i> Record & generate receipt</button>
            <a href="{{ route('fees.payments.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
