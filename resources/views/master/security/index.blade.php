@extends('layouts.app')
@section('title', 'Security')

@section('content')
<h3 class="mb-4">Security &amp; roles</h3>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white"><strong>User roles</strong></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>User</th><th>Email</th><th>Current role</th><th>Change</th></tr></thead>
                    <tbody>
                    @foreach($users as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge bg-{{ $u->role === 'super_admin' ? 'primary' : 'secondary' }}">{{ str_replace('_', ' ', $u->role) }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('master.security.role', $u) }}" class="d-flex gap-2">
                                    @csrf
                                    <select name="role" class="form-select form-select-sm w-auto">
                                        @foreach($roles as $r)
                                            <option value="{{ $r }}" @selected($u->role === $r)>{{ $r }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-white"><strong>Permission catalogue</strong></div>
            <div class="card-body">
                <p class="small text-muted">These are the actions the SaaS exposes. The current build gates the whole apex by <code>isSuperAdmin()</code>; finer-grained per-permission checks can be wired to these names in policy classes.</p>
                <table class="table table-sm small">
                    <thead><tr><th>Resource</th><th>Actions</th></tr></thead>
                    <tbody>
                    @foreach($permissions as $resource => $actions)
                        <tr>
                            <td><code>{{ $resource }}</code></td>
                            <td>{{ implode(', ', $actions) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
