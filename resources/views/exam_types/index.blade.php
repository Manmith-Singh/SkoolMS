@extends('layouts.app')
@section('title', 'Exam Types')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Exam Types</h4>
    <a href="{{ route('exam-types.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New Exam Type</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr><th>Name</th><th>Description</th><th>Exams</th><th class="no-sort">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($examTypes as $t)
                <tr>
                    <td>{{ $t->name }}</td>
                    <td>{{ $t->description ?? '—' }}</td>
                    <td>{{ $t->exams()->count() }}</td>
                    <td>
                        <a href="{{ route('exam-types.edit', $t) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('exam-types.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Delete this exam type?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $examTypes->links() }}</div>
</div>
@endsection
