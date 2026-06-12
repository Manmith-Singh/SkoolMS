@extends('layouts.app')
@section('title', 'New user')

@section('content')
<h3 class="mb-4">New user</h3>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('master.users.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="text" name="password" value="{{ old('password', \Illuminate\Support\Str::random(12)) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm password <span class="text-danger">*</span></label>
                    <input type="text" name="password_confirmation" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select" required>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" @selected(old('role') === $r)>{{ ucfirst(str_replace('_', ' ', $r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tenant (for non-super_admin roles)</label>
                    <select name="tenant_id" class="form-select">
                        <option value="">— None (SuperAdmin) —</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" @selected(old('tenant_id') == $t->id)>{{ $t->name }} ({{ $t->subdomain }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Create user</button>
                <a href="{{ route('master.users.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
