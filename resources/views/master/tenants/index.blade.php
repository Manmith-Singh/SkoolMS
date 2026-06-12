@extends('layouts.app')
@section('title', 'Tenants')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Tenant Management</h3>
    <a href="{{ route('master.tenants.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> New School
    </a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card stat-card p-3"><div class="stat-label">Total</div><div class="stat-value">{{ $stats['total'] }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3" style="border-left-color:#198754;"><div class="stat-label">Active</div><div class="stat-value text-success">{{ $stats['active'] }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3" style="border-left-color:#dc3545;"><div class="stat-label">Suspended</div><div class="stat-value text-danger">{{ $stats['suspended'] }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3" style="border-left-color:#ffc107;"><div class="stat-label">Trialing</div><div class="stat-value text-warning">{{ $stats['trialing'] }}</div></div></div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="name, subdomain, email">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach (['active', 'suspended', 'trial'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
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
                    <th>#</th><th>Name</th><th>Subdomain</th><th>DB</th><th>Plan</th><th>Status</th><th>Created</th><th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($tenants as $t)
                <tr>
                    <td>{{ $t->id }}</td>
                    <td><a href="{{ route('master.tenants.show', $t) }}">{{ $t->name }}</a></td>
                    <td><a href="{{ $t->url() }}" target="_blank"><code>{{ $t->subdomain }}</code></a></td>
                    <td><code>{{ $t->db_name }}</code></td>
                    <td>{{ $t->plan->name ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $t->status === 'active' ? 'success' : ($t->status === 'suspended' ? 'danger' : 'secondary') }}">{{ $t->status }}</span>
                    </td>
                    <td>{{ $t->created_at->format('Y-m-d') }}</td>
                    <td class="text-end">
                        <a href="{{ route('master.tenants.edit', $t) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                        @if($t->status === 'active')
                            <form method="POST" action="{{ route('master.tenants.suspend', $t) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-warning" onclick="return confirm('Suspend this school?')"><i class="fas fa-pause"></i></button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('master.tenants.activate', $t) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-success"><i class="fas fa-play"></i></button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('master.tenants.destroy', $t) }}" class="d-inline" onsubmit="return confirm('PERMANENTLY delete this tenant and its database?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $tenants->links() }}</div>
</div>
@endsection
