@extends('layouts.app')
@section('title', 'Exams')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Exams</h4>
    <a href="{{ route('exams.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add exam</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr><th>Name</th><th>Class</th><th>Subject</th><th>Date</th><th>Max</th><th>Pass</th><th class="no-sort">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($exams as $e)
                <tr>
                    <td>{{ $e->name }}</td>
                    <td>
                        @if($e->classes->isNotEmpty())
                            @foreach($e->classes as $c)
                                <span class="badge bg-secondary me-1">{{ $c->display_name }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $e->subject->name ?? '—' }}</td>
                    <td>{{ $e->date->format('d M Y') }}</td>
                    <td>{{ $e->max_marks }}</td>
                    <td>{{ $e->pass_marks }}</td>
                    <td>
                        <a href="{{ route('exams.show', $e) }}" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('results.edit', $e) }}" class="btn btn-sm btn-outline-success" title="Enter marks"><i class="fas fa-pen"></i></a>
                        <a href="{{ route('exams.edit', $e) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('exams.destroy', $e) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $exams->links() }}</div>
</div>
@endsection
