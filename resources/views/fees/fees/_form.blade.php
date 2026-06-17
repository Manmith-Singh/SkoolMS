<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Category *</label>
        <select name="category_id" class="form-select" required>
            <option value="">—</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id) data-amount="{{ $cat->default_amount }}">{{ $cat->name }} ({{ ucfirst(str_replace('_',' ', $cat->frequency)) }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Amount *</label>
        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Due date *</label>
        <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(15)->format('Y-m-d')) }}" class="form-control" required>
    </div>

    <div class="col-12">
        <label class="form-label">Assign to *</label>
        <div class="btn-group w-100" role="group">
            <input type="radio" class="btn-check" name="assignment" id="aAll" value="all" @checked(old('assignment', 'all') === 'all')>
            <label class="btn btn-outline-primary" for="aAll">All students</label>

            <input type="radio" class="btn-check" name="assignment" id="aClass" value="class" @checked(old('assignment') === 'class')>
            <label class="btn btn-outline-primary" for="aClass">Whole class</label>

            <input type="radio" class="btn-check" name="assignment" id="aStudent" value="student" @checked(old('assignment') === 'student')>
            <label class="btn btn-outline-primary" for="aStudent">Single student</label>
        </div>
    </div>

    <div class="col-md-6" id="classBox" style="display:none;">
        <label class="form-label">Class & Section</label>
        @include('partials._class_section_fields', [
            'name'     => 'class_id',
            'classes'  => $classes,
            'selected' => old('class_id') !== null ? (array) old('class_id') : [],
        ])
    </div>

    <div class="col-md-6" id="studentBox" style="display:none;">
        <label class="form-label">Student</label>
        <select name="student_id" class="form-select">
            <option value="">—</option>
            @foreach($students as $s)
                <option value="{{ $s->id }}" @selected(old('student_id') == $s->id)>{{ $s->full_name }} ({{ $s->admission_no }})</option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
    </div>
</div>

<script>
(function() {
    const toggle = () => {
        const val = document.querySelector('input[name="assignment"]:checked').value;
        document.getElementById('classBox').style.display   = val === 'class' ? 'block' : 'none';
        document.getElementById('studentBox').style.display = val === 'student' ? 'block' : 'none';
    };
    document.querySelectorAll('input[name="assignment"]').forEach(r => r.addEventListener('change', toggle));
    toggle();

    // pre-fill amount from category default
    const cat = document.querySelector('select[name="category_id"]');
    const amt = document.querySelector('input[name="amount"]');
    cat && cat.addEventListener('change', e => {
        const opt = e.target.selectedOptions[0];
        if (opt && opt.dataset.amount && !amt.value) amt.value = opt.dataset.amount;
    });
})();
</script>
