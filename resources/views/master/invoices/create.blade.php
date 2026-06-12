@extends('layouts.app')
@section('title', 'New invoice')

@section('content')
<h3 class="mb-4">Create invoice</h3>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('master.invoices.store') }}">
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
        <div class="col-md-3">
            <label class="form-label">Currency</label>
            <input type="text" name="currency" value="{{ old('currency', 'USD') }}" maxlength="3" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Issue date</label>
            <input type="date" name="issue_date" value="{{ old('issue_date', now()->format('Y-m-d')) }}" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Due date</label>
            <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(14)->format('Y-m-d')) }}" class="form-control" required>
        </div>
    </div>

    <h5 class="mt-4 mb-2 text-muted">Line items</h5>
    <table class="table" id="items">
        <thead>
            <tr><th>Description</th><th width="120">Qty</th><th width="180">Unit price</th><th width="60"></th></tr>
        </thead>
        <tbody>
            <tr>
                <td><input name="items[0][description]" class="form-control" required></td>
                <td><input type="number" name="items[0][quantity]" value="1" min="1" class="form-control" required></td>
                <td><input type="number" step="0.01" name="items[0][unit_price]" value="0" min="0" class="form-control" required></td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addItem()"><i class="fas fa-plus me-1"></i>Add row</button>

    <div class="mt-4">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
    </div>

    <div class="mt-4">
        <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Create invoice</button>
        <a href="{{ route('master.invoices.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>

@push('scripts')
<script>
let idx = 1;
function addItem() {
    const tbody = document.querySelector('#items tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input name="items[${idx}][description]" class="form-control" required></td>
        <td><input type="number" name="items[${idx}][quantity]" value="1" min="1" class="form-control" required></td>
        <td><input type="number" step="0.01" name="items[${idx}][unit_price]" value="0" min="0" class="form-control" required></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
    idx++;
}
</script>
@endpush
@endsection
