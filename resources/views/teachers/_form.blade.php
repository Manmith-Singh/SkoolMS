@php $val = fn ($k, $d = '') => old($k, $teacher->{$k} ?? $d); @endphp
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Employee ID</label>
        <input type="text" name="employee_id" value="{{ $val('employee_id') }}" class="form-control" placeholder="auto-generated if empty">
    </div>
    <div class="col-md-3">
        <label class="form-label">First name *</label>
        <input type="text" name="first_name" value="{{ $val('first_name') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Last name *</label>
        <input type="text" name="last_name" value="{{ $val('last_name') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select">
            <option value="">—</option>
            @foreach(['male','female','other'] as $g)
                <option value="{{ $g }}" @selected($val('gender') === $g)>{{ ucfirst($g) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Email *</label>
        <input type="email" name="email" value="{{ $val('email') }}" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" value="{{ $val('phone') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Subject</label>
        <select name="subject_id" class="form-select">
            <option value="">—</option>
            @foreach($subjects as $s)
                <option value="{{ $s->id }}" @selected($val('subject_id') == $s->id)>{{ $s->name }} ({{ $s->schoolClass->display_name ?? '' }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Qualification</label>
        <input type="text" name="qualification" value="{{ $val('qualification') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Hire date</label>
        <input type="date" name="hire_date" value="{{ $val('hire_date') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Salary</label>
        <input type="number" step="0.01" name="salary" value="{{ $val('salary') }}" class="form-control">
    </div>
    <div class="col-md-12">
        <label class="form-label">Address</label>
        <input type="text" name="address" value="{{ $val('address') }}" class="form-control">
    </div>
</div>
