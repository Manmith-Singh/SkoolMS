@extends('layouts.app')
@section('title', 'Expenditure types')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Expenditure types</h4>
    <a href="{{ route('expenditure-types.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add type</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr><th>Name</th><th>Description</th><th>Status</th><th class="no-sort">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($types as $t)
                <tr>
                    <td>{{ $t->name }}</td>
                    <td>{{ $t->description ?? '—' }}</td>
                    <td><span class="badge bg-{{ $t->is_active ? 'success' : 'secondary' }}">{{ $t->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('expenditure-types.edit', $t) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('expenditure-types.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $types->links() }}</div>
</div>
@endsection
