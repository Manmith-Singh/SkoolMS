@php $val = fn ($k, $d = '') => old($k, $category->{$k} ?? $d); @endphp
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Name *</label>
        <input type="text" name="name" value="{{ $val('name') }}" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Default amount *</label>
        <input type="number" step="0.01" min="0" name="default_amount" value="{{ $val('default_amount', 0) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Frequency *</label>
        <select name="frequency" class="form-select" required>
            @foreach(['one_time','monthly','quarterly','half_yearly','annually'] as $f)
                <option value="{{ $f }}" @selected($val('frequency') === $f)>{{ ucfirst(str_replace('_',' ', $f)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="ia{{ $category->id ?? 'new' }}" @checked($val('is_active', 1))>
            <label class="form-check-label" for="ia{{ $category->id ?? 'new' }}">Active</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ $val('description') }}</textarea>
    </div>
</div>
