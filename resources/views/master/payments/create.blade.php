@extends('layouts.app')
@section('title', 'Record payment')

@section('content')
<h3 class="mb-4">Record payment</h3>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('master.payments.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Tenant <span class="text-danger">*</span></label>
            <select name="tenant_id" class="form-select" required>
                <option value="">— select school —</option>
                @foreach($tenants as $t)
                    <option value="{{ $t->id }}" @selected(old('tenant_id') == $t->id)>{{ $t->name }} ({{ $t->subdomain }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Invoice (optional)</label>
            <select name="invoice_id" class="form-select">
                <option value="">— none —</option>
                @foreach($invoices as $i)
                    <option value="{{ $i->id }}" @selected(old('invoice_id') == $i->id)>{{ $i->invoice_number }} — {{ $i->tenant->name }} ({{ number_format($i->total, 2) }} {{ $i->currency }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Amount <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" min="0" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Currency</label>
            <input type="text" name="currency" value="{{ old('currency', 'USD') }}" maxlength="3" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Method</label>
            <select name="method" class="form-select" required>
                @foreach(['cash', 'bank_transfer', 'cheque', 'card', 'online', 'other'] as $m)
                    <option value="{{ $m }}" @selected(old('method') === $m)>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <option value="succeeded" @selected(old('status', 'succeeded') === 'succeeded')>Succeeded</option>
                <option value="pending" @selected(old('status') === 'pending')>Pending</option>
                <option value="failed" @selected(old('status') === 'failed')>Failed</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Reference</label>
            <input type="text" name="reference" value="{{ old('reference') }}" class="form-control" placeholder="cheque #, txn id, etc.">
        </div>
        <div class="col-md-6">
            <label class="form-label">Paid at</label>
            <input type="datetime-local" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" class="form-control">
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
        </div>
    </div>
    <div class="mt-4">
        <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Record payment</button>
        <a href="{{ route('master.payments.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>
@endsection
