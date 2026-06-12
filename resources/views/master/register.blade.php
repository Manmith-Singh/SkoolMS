@extends('layouts.auth')
@section('content')
<h4 class="mb-3 text-center">Register your school</h4>
<p class="text-muted small text-center mb-4">
    Pick a unique subdomain — your school will live at <code>{{ '{subdomain}.' . config('tenancy.domain_base') }}</code>.
</p>

<form method="POST" action="{{ route('master.register.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">School name</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Subdomain</label>
        <div class="input-group">
            <input type="text" name="subdomain" value="{{ old('subdomain') }}" class="form-control" pattern="[a-z0-9](?:[a-z0-9\-]{{0,61}}[a-z0-9])?" required>
            <span class="input-group-text">.{{ config('tenancy.domain_base') }}</span>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">MySQL database name <span class="text-danger">*</span></label>
        <input type="text" name="db_name" value="{{ old('db_name') }}" class="form-control" placeholder="e.g. u114510322_skoolms_bmhs" required>
        <div class="form-text">Exact MySQL database name from cPanel (including prefix). Must already exist.</div>
    </div>
    <div class="mb-3">
        <label class="form-label">MySQL username</label>
        <input type="text" name="db_user" value="{{ old('db_user') }}" class="form-control" placeholder="e.g. u114510322_bmhs_admin">
        <div class="form-text">If different from shared user.</div>
    </div>
    <div class="mb-3">
        <label class="form-label">MySQL password</label>
        <input type="text" name="db_password" value="{{ old('db_password') }}" class="form-control" placeholder="Leave blank to use shared user">
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Admin name</label>
            <input type="text" name="admin_name" value="{{ old('admin_name') }}" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Admin email</label>
            <input type="email" name="admin_email" value="{{ old('admin_email') }}" class="form-control" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="admin_password" class="form-control" minlength="8" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Confirm password</label>
            <input type="password" name="admin_password_confirmation" class="form-control" minlength="8" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Contact phone</label>
            <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Address</label>
            <input type="text" name="address" value="{{ old('address') }}" class="form-control">
        </div>
    </div>

    <button class="btn btn-primary w-100" type="submit">
        <i class="fas fa-rocket me-1"></i> Provision my school
    </button>
</form>

<div class="text-center mt-3">
    <a href="{{ route('master.login') }}" class="text-decoration-none">Already have an account? Sign in</a>
</div>
@endsection
