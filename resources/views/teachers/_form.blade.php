@php
    $val = fn ($k, $d = '') => old($k, $teacher->{$k} ?? $d);
    $selectedSubjectIds = old('subject_id') !== null
        ? (array) old('subject_id')
        : ($teacher && $teacher->exists ? $teacher->subjects->pluck('id')->all() : []);
@endphp
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
    <div class="col-12"><hr><h6 class="text-muted">Payroll Details</h6></div>
    <div class="col-md-3">
        <label class="form-label">PF Number</label>
        <input type="text" name="pf_number" value="{{ $val('pf_number') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">ESI Number</label>
        <input type="text" name="esi_number" value="{{ $val('esi_number') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">UAN Number</label>
        <input type="text" name="uan_number" value="{{ $val('uan_number') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Bank Account</label>
        <input type="text" name="bank_account" value="{{ $val('bank_account') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">IFSC Code</label>
        <input type="text" name="ifsc_code" value="{{ $val('ifsc_code') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Basic Pay</label>
        <input type="number" step="0.01" name="basic_pay" value="{{ $val('basic_pay') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">HRA</label>
        <input type="number" step="0.01" name="hra" value="{{ $val('hra') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">DA</label>
        <input type="number" step="0.01" name="da" value="{{ $val('da') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Conveyance</label>
        <input type="number" step="0.01" name="conveyance" value="{{ $val('conveyance') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Medical Allowance</label>
        <input type="number" step="0.01" name="medical_allowance" value="{{ $val('medical_allowance') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Other Allowances</label>
        <input type="number" step="0.01" name="other_allowances" value="{{ $val('other_allowances') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(['working','resigned','transfer'] as $s)
                <option value="{{ $s }}" @selected($val('status', 'working') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Class Teacher</label>
        <select name="class_teacher_id" class="form-select">
            <option value="">—</option>
            @foreach($classes as $c)
                <option value="{{ $c->id }}" @selected($val('class_teacher_id') == $c->id)>{{ $c->display_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-12">
        @include('partials._subject_multiselect', [
            'subjects'  => $subjects,
            'selected'  => $selectedSubjectIds,
        ])
    </div>
    <div class="col-md-12">
        <label class="form-label">Address</label>
        <input type="text" name="address" value="{{ $val('address') }}" class="form-control">
    </div>
</div>
