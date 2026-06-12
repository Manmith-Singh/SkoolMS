@php $val = fn ($k, $d = '') => old($k, $exam ? ($exam->{$k} instanceof \Carbon\Carbon ? $exam->{$k}->format('Y-m-d') : $exam->{$k}) : $d); @endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Exam name *</label>
        <input type="text" name="name" value="{{ $val('name') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Class (Multi-Select) *</label>
        @include('partials._class_select', [
            'name'     => 'class_id',
            'classes'  => $classes,
            'selected' => old('class_id') !== null
                ? (array) old('class_id')
                : ($exam && $exam->exists
                    ? $exam->classes->pluck('id')->all()
                    : ($exam?->class_id ? [$exam->class_id] : [])),
            'required' => true,
            'size'     => 4,
        ])
    </div>
    <div class="col-md-3">
        <label class="form-label">Subject *</label>
        <select name="subject_id" class="form-select" required>
            <option value="">—</option>
            @foreach($subjects as $s)
                <option value="{{ $s->id }}" @selected($val('subject_id') == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Date *</label>
        <input type="date" name="date" value="{{ $val('date') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Max marks *</label>
        <input type="number" step="0.01" min="1" name="max_marks" value="{{ $val('max_marks', 100) }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Pass marks *</label>
        <input type="number" step="0.01" min="0" name="pass_marks" value="{{ $val('pass_marks', 33) }}" class="form-control" required>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ $val('description') }}</textarea>
    </div>
</div>
