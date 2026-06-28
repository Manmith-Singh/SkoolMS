@php
    $val = fn ($k, $d = '') => old($k, $student->{$k} ?? $d);
@endphp
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Admission no</label>
        <input type="text" name="admission_no" value="{{ $val('admission_no') }}" class="form-control" placeholder="auto-generated if empty">
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
        <label class="form-label">Roll no</label>
        <input type="text" name="roll_no" value="{{ $val('roll_no') }}" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label">Date of birth</label>
        <input type="date" name="dob" value="{{ $val('dob') }}" class="form-control">
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
    <div class="col-md-3">
        @include('partials._class_section_fields', [
            'name'     => 'class_id',
            'classes'  => $classes,
            'selected' => old('class_id') !== null
                ? (array) old('class_id')
                : ($student?->class_id ? [$student->class_id] : []),
        ])
    </div>
    <div class="col-md-3">
        <label class="form-label">Admission date</label>
        <input type="date" name="admission_date" value="{{ $val('admission_date') }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ $val('email') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" value="{{ $val('phone') }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Guardian</label>
        <input type="text" name="guardian_name" value="{{ $val('guardian_name') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Guardian phone</label>
        <input type="text" name="guardian_phone" value="{{ $val('guardian_phone') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Father name</label>
        <input type="text" name="father_name" value="{{ $val('father_name') }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Mother name</label>
        <input type="text" name="mother_name" value="{{ $val('mother_name') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">PEN ID</label>
        <input type="text" name="pen_id" value="{{ $val('pen_id') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Caste</label>
        <select name="caste" class="form-select">
            <option value="">—</option>
            @foreach(App\Models\Tenant\Student::CASTES as $c)
                <option value="{{ $c }}" @selected($val('caste') === $c)>{{ $c }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Aadhaar number</label>
        <input type="text" name="aadhaar_number" value="{{ $val('aadhaar_number') }}" class="form-control" maxlength="12" placeholder="12-digit">
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(['active','inactive','drop'] as $s)
                <option value="{{ $s }}" @selected($val('status', 'active') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-8">
        <label class="form-label">Address</label>
        <input type="text" name="address" value="{{ $val('address') }}" class="form-control">
    </div>
</div>
