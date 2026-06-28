@extends('layouts.app')
@section('title', 'Students')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Students</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('students.import') }}" class="btn btn-outline-primary">
            <i class="fas fa-file-upload me-1"></i> Bulk import
        </a>
        <a href="{{ route('students.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add student
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name or admission no">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Class</label>
                <select name="class_id" class="form-select">
                    <option value="">All classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" @selected(request('class_id') == $c->id)>{{ $c->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-search"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('students.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr>
                    <th class="no-sort">Actions</th>
                    <th>Adm No</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Roll</th>
                    <th>Father</th>
                    <th>Mother</th>
                    <th>PEN ID</th>
                    <th>Caste</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                <tr>
                    <td>
                        <a href="{{ route('students.edit', $s) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('students.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Delete this student?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                    <td><code>{{ $s->admission_no }}</code></td>
                    <td><a href="{{ route('students.show', $s) }}">{{ $s->full_name }}</a></td>
                    <td>{{ $s->schoolClass->display_name ?? '—' }}</td>
                    <td>{{ $s->roll_no ?? '—' }}</td>
                    <td>{{ $s->father_name ?? '—' }}</td>
                    <td>{{ $s->mother_name ?? '—' }}</td>
                    <td>{{ $s->pen_id ?? '—' }}</td>
                    <td>{{ $s->caste ?? '—' }}</td>
                    <td>{{ $s->guardian_phone ?? $s->phone ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
