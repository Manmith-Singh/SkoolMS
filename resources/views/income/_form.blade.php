@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Type *</label>
        <select name="income_type_id" class="form-select" required>
            <option value="">—</option>
            @foreach($types as $t)
                <option value="{{ $t->id }}" @selected(old('income_type_id', $income->income_type_id ?? '') == $t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Amount *</label>
        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $income->amount ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Date *</label>
        <input type="date" name="date" value="{{ old('date', $income->date ?? today()->toDateString()) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $income->description ?? '') }}</textarea>
    </div>
    <div class="col-md-3">
        <label class="form-label">Reference</label>
        <input type="text" name="reference" value="{{ old('reference', $income->reference ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Received by</label>
        <input type="text" name="received_by" value="{{ old('received_by', $income->received_by ?? '') }}" class="form-control">
    </div>
</div>
