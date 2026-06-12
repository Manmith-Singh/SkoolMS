@extends('layouts.app')
@section('title', 'Subjects')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Subjects</h4>
    <a href="{{ route('subjects.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add subject</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <select name="class_id" class="form-select">
                    <option value="">All classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" @selected(request('class_id') == $c->id)>{{ $c->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr><th>Name</th><th>Code</th><th>Class</th><th>Description</th><th class="no-sort">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($subjects as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td><code>{{ $s->code ?? '—' }}</code></td>
                    <td>
                        @if($s->classes->isNotEmpty())
                            @foreach($s->classes as $c)
                                <span class="badge bg-secondary me-1">{{ $c->display_name }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ Str::limit($s->description, 60) }}</td>
                    <td>
                        <a href="{{ route('subjects.edit', $s) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('subjects.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $subjects->links() }}</div>
</div>
@endsection
