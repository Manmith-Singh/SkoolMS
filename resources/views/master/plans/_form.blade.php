@php
    $featuresText = old('features', is_array($plan?->features) ? implode("\n", $plan->features) : null);
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $plan->name ?? null) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Slug (auto if blank)</label>
        <input type="text" name="slug" value="{{ old('slug', $plan->slug ?? null) }}" class="form-control">
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $plan->description ?? null) }}</textarea>
    </div>
    <div class="col-md-3">
        <label class="form-label">Price <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price ?? 0) }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Currency</label>
        <input type="text" name="currency" value="{{ old('currency', $plan->currency ?? 'USD') }}" maxlength="3" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Billing period</label>
        <select name="billing_period" class="form-select" required>
            @foreach(['monthly', 'quarterly', 'yearly'] as $p)
                <option value="{{ $p }}" @selected(old('billing_period', $plan->billing_period ?? 'monthly') === $p)>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Sort order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Max students (0 = unlimited)</label>
        <input type="number" name="max_students" value="{{ old('max_students', $plan->max_students ?? 0) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Max teachers (0 = unlimited)</label>
        <input type="number" name="max_teachers" value="{{ old('max_teachers', $plan->max_teachers ?? 0) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Max storage MB (0 = unlimited)</label>
        <input type="number" name="max_storage_mb" value="{{ old('max_storage_mb', $plan->max_storage_mb ?? 0) }}" class="form-control">
    </div>
    <div class="col-12">
        <label class="form-label">Features (one per line)</label>
        <textarea name="features" rows="4" class="form-control" placeholder="Online exams&#10;Bulk import&#10;Email reports">{{ $featuresText }}</textarea>
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $plan->is_active ?? true))>
            <label class="form-check-label" for="is_active">Plan is active and bookable</label>
        </div>
    </div>
</div>
