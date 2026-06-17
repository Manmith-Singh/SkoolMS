@php $val = fn ($k, $d = '') => old($k, $subject?->{$k} ?? $d); @endphp
<div class="row g-3">
    <div class="col-md-5">
        <label class="form-label">Name *</label>
        <input type="text" name="name" value="{{ $val('name') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Code</label>
        <input type="text" name="code" value="{{ $val('code') }}" class="form-control">
    </div>
    <div class="col-md-4">
        @include('partials._class_section_fields', [
            'name'     => 'class_id',
            'classes'  => $classes,
            'selected' => old('class_id') !== null
                ? (array) old('class_id')
                : ($subject && $subject->exists
                    ? $subject->classes->pluck('id')->all()
                    : ($subject?->class_id ? [$subject->class_id] : [])),
        ])
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ $val('description') }}</textarea>
    </div>
</div>
