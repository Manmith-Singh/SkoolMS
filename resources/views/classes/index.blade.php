@extends('layouts.app')
@section('title', 'Classes')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Classes</h4>
    <a href="{{ route('classes.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add class</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr><th>Name</th><th>Section</th><th>Capacity</th><th>Students</th><th>Description</th><th class="no-sort">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($classes as $c)
                <tr>
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->section ?? '—' }}</td>
                    <td>{{ $c->capacity }}</td>
                    <td>{{ $c->students_count }}</td>
                    <td>{{ Str::limit($c->description, 50) }}</td>
                    <td>
                        <a href="{{ route('classes.edit', $c) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('classes.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $classes->links() }}</div>
</div>
@endsection
