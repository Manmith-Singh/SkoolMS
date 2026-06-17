@php
    $val = fn ($k, $d = '') => old($k, $exam && $exam->exists ? ($exam->{$k} instanceof \Carbon\Carbon ? $exam->{$k}->format('Y-m-d') : $exam->{$k}) : $d);
    $selectedClasses = old('class_id') !== null
        ? (array) old('class_id')
        : ($exam && $exam->exists
            ? $exam->classes->pluck('id')->all()
            : ($exam?->class_id ? [$exam->class_id] : []));
    $examSubjectIds = $exam && $exam->exists ? $exam->subjects->pluck('id')->all() : [];
@endphp
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label">Exam name *</label>
        <input type="text" name="name" value="{{ $val('name') }}" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Exam type *</label>
        <select name="exam_type_id" class="form-select" required>
            <option value="">— Select —</option>
            @foreach($examTypes as $t)
                <option value="{{ $t->id }}" @selected($val('exam_type_id') == $t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        @include('partials._class_section_fields', [
            'name'     => 'class_id',
            'classes'  => $classes,
            'selected' => $selectedClasses,
        ])
    </div>
    <div class="col-md-3">
        <label class="form-label">From date *</label>
        <input type="date" name="from_date" value="{{ $val('from_date') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">To date *</label>
        <input type="date" name="to_date" value="{{ $val('to_date') }}" class="form-control" required>
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

<h5 class="mb-2">Subject schedule</h5>
<p class="text-muted small mb-2">Select classes above to show their subjects, then set the date, portion, and order for each.</p>
<div class="table-responsive">
    <table class="table table-bordered" id="subject-table">
        <thead>
            <tr>
                <th style="width:25%">Subject</th>
                <th style="width:20%">Date *</th>
                <th style="width:40%">Notes / Portion</th>
                <th style="width:15%">Order</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjects as $subject)
                @php
                    $pivot = $exam && $exam->exists ? $exam->subjects->find($subject->id) : null;
                @endphp
                <tr class="subject-row" data-classes="{{ $subject->classes->pluck('id')->join(',') }}"
                    @if(!in_array($subject->id, $examSubjectIds)) style="display:none" @endif>
                    <td>{{ $subject->name }}</td>
                    <td>
                        <input type="date" name="subjects[{{ $subject->id }}][date]"
                               class="form-control"
                               value="{{ old("subjects.{$subject->id}.date", $pivot?->pivot->date ?? '') }}">
                    </td>
                    <td>
                        <textarea name="subjects[{{ $subject->id }}][notes]"
                                  class="form-control" rows="1">{{ old("subjects.{$subject->id}.notes", $pivot?->pivot->notes ?? '') }}</textarea>
                    </td>
                    <td>
                        <input type="number" name="subjects[{{ $subject->id }}][order]"
                               class="form-control" min="0" value="{{ old("subjects.{$subject->id}.order", $pivot?->pivot->order ?? 0) }}">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function filterSubjects() {
        var checked = document.querySelectorAll('input[name="class_id[]"]:checked');
        var selected = Array.from(checked).map(function (c) { return parseInt(c.value); });

        document.querySelectorAll('.subject-row').forEach(function (row) {
            var classList = (row.getAttribute('data-classes') || '').split(',').map(Number);
            var visible = classList.some(function (c) { return selected.includes(c); });
            row.style.display = visible ? '' : 'none';
            row.querySelectorAll('input, textarea').forEach(function (el) {
                el.disabled = !visible;
            });
        });
    }

    document.querySelectorAll('input[name="class_id[]"]').forEach(function (cb) {
        cb.addEventListener('change', filterSubjects);
    });

    filterSubjects();
});
</script>
@endpush
