@csrf
<div class="mb-3">
    <label class="form-label">Name *</label>
    <input type="text" name="name" value="{{ old('name', $expenditureType->name ?? '') }}" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="2">{{ old('description', $expenditureType->description ?? '') }}</textarea>
</div>
<div class="mb-3 form-check">
    <input type="checkbox" name="is_active" class="form-check-input" value="1" id="is_active" @checked(old('is_active', $expenditureType->is_active ?? true))>
    <label class="form-check-label" for="is_active">Active</label>
</div>
