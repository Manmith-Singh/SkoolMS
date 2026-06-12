@extends('layouts.app')
@section('title', 'Teachers')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Teachers</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('teachers.import') }}" class="btn btn-outline-primary">
            <i class="fas fa-file-upload me-1"></i> Bulk import
        </a>
        <a href="{{ route('teachers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add teacher
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name / email / ID">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-search"></i> Search</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr>
                    <th>Emp ID</th>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Qualification</th>
                    <th class="no-sort">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teachers as $t)
                <tr>
                    <td><code>{{ $t->employee_id }}</code></td>
                    <td>{{ $t->full_name }}</td>
                    <td>{{ $t->subject->name ?? '—' }}</td>
                    <td>{{ $t->email }}</td>
                    <td>{{ $t->phone ?? '—' }}</td>
                    <td>{{ $t->qualification ?? '—' }}</td>
                    <td>
                        <a href="{{ route('teachers.edit', $t) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('teachers.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Delete this teacher?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $teachers->links() }}</div>
</div>
@endsection
