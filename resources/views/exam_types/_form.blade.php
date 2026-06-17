@php $val = fn ($k, $d = '') => old($k, $examType?->{$k} ?? $d); @endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name *</label>
        <input type="text" name="name" value="{{ $val('name') }}" class="form-control" required maxlength="100">
    </div>
    <div class="col-md-6">
        <label class="form-label">Description</label>
        <input type="text" name="description" value="{{ $val('description') }}" class="form-control" maxlength="500">
    </div>
</div>
