@php $val = fn ($k, $d = '') => old($k, $class->{$k} ?? $d); @endphp
<div class="row g-3">
    <div class="col-md-5">
        <label class="form-label">Name *</label>
        <input type="text" name="name" value="{{ $val('name') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Section</label>
        <input type="text" name="section" value="{{ $val('section') }}" class="form-control" placeholder="A, B, C…">
    </div>
    <div class="col-md-4">
        <label class="form-label">Capacity</label>
        <input type="number" name="capacity" value="{{ $val('capacity', 40) }}" class="form-control" min="1">
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ $val('description') }}</textarea>
    </div>
</div>
