@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Type *</label>
        <select name="expenditure_type_id" class="form-select" required>
            <option value="">—</option>
            @foreach($types as $t)
                <option value="{{ $t->id }}" @selected(old('expenditure_type_id', $expenditure->expenditure_type_id ?? '') == $t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Amount *</label>
        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $expenditure->amount ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Date *</label>
        <input type="date" name="date" value="{{ old('date', $expenditure->date ?? today()->toDateString()) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $expenditure->description ?? '') }}</textarea>
    </div>
    <div class="col-md-3">
        <label class="form-label">Reference</label>
        <input type="text" name="reference" value="{{ old('reference', $expenditure->reference ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Paid by</label>
        <input type="text" name="paid_by" value="{{ old('paid_by', $expenditure->paid_by ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Approved by</label>
        <input type="text" name="approved_by" value="{{ old('approved_by', $expenditure->approved_by ?? '') }}" class="form-control">
    </div>
</div>
