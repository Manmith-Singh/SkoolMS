@extends('layouts.app')
@section('title', 'Fee categories')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Fee categories</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCat">
        <i class="fas fa-plus me-1"></i> Add category
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr><th>Name</th><th>Default amount</th><th>Frequency</th><th>Status</th><th class="no-sort">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($categories as $c)
                <tr>
                    <td>{{ $c->name }}<br><small class="text-muted">{{ $c->description }}</small></td>
                    <td>{{ number_format($c->default_amount, 2) }}</td>
                    <td><span class="badge bg-info">{{ ucfirst(str_replace('_',' ', $c->frequency)) }}</span></td>
                    <td>
                        <span class="badge bg-{{ $c->is_active ? 'success' : 'secondary' }}">
                            {{ $c->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('fees.categories.edit', $c) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('fees.categories.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $categories->links() }}</div>
</div>

<div class="modal fade" id="addCat" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('fees.categories.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">New fee category</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @include('fees.categories._form', ['category' => null])
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
