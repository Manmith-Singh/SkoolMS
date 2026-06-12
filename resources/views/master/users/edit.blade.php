@extends('layouts.app')
@section('title', 'Edit user')

@section('content')
<h3 class="mb-4">Edit user — {{ $user->name }}</h3>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('master.users.update', $user) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">New password (leave blank to keep)</label>
                    <input type="text" name="password" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm new password</label>
                    <input type="text" name="password_confirmation" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" @selected(old('role', $user->role) === $r)>{{ ucfirst(str_replace('_', ' ', $r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tenant</label>
                    <select name="tenant_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" @selected(old('tenant_id', $user->tenant_id) == $t->id)>{{ $t->name }} ({{ $t->subdomain }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save changes</button>
                <a href="{{ route('master.users.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
