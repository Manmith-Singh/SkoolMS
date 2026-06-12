@extends('layouts.app')
@section('title', 'Users')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">User Management</h3>
    <a href="{{ route('master.users.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New user</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="name or email">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Role</label>
                <select name="role" class="form-select">
                    <option value="">All</option>
                    @foreach (['super_admin', 'admin', 'receptionist', 'teacher', 'student'] as $r)
                        <option value="{{ $r }}" @selected(request('role') === $r)>{{ ucfirst(str_replace('_', ' ', $r)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="super_only" value="1" id="super_only" @checked(request('super_only'))>
                    <label class="form-check-label" for="super_only">Super-admins only</label>
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0 datatable">
            <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Tenant</th><th>Created</th><th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td><span class="badge bg-{{ $u->role === 'super_admin' ? 'primary' : 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $u->role)) }}</span></td>
                    <td>{{ $u->tenant->name ?? '—' }}</td>
                    <td>{{ $u->created_at->format('Y-m-d') }}</td>
                    <td class="text-end">
                        <a href="{{ route('master.users.edit', $u) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                        @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('master.users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Delete this user?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $users->links() }}</div>
</div>
@endsection
