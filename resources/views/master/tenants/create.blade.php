@extends('layouts.app')
@section('title', 'New School')

@section('content')
<h3 class="mb-4">Register a new school</h3>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('master.tenants.store') }}">
            @csrf

            <h5 class="mt-3 mb-3 text-muted">School</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">School name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subdomain <span class="text-danger">*</span></label>
                    <input type="text" name="subdomain" value="{{ old('subdomain') }}" class="form-control" placeholder="e.g. lba" required>
                    <div class="form-text">Will be accessible at <code>{{ '{subdomain}.'.config('tenancy.app_domain') }}</code></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">MySQL database name <span class="text-danger">*</span></label>
                    <input type="text" name="db_name" value="{{ old('db_name') }}" class="form-control" placeholder="e.g. u114510322_skoolms_bmhs" required>
                    <div class="form-text">Exact name from cPanel (including Hostinger prefix). Must already exist.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">MySQL username</label>
                    <input type="text" name="db_user" value="{{ old('db_user') }}" class="form-control" placeholder="e.g. u114510322_bmhs_admin">
                    <div class="form-text">If different from master DB user.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">MySQL password</label>
                    <input type="text" name="db_password" value="{{ old('db_password') }}" class="form-control" placeholder="Leave blank to use shared user">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Plan</label>
                    <select name="plan_id" class="form-select">
                        <option value="">— No plan —</option>
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}" @selected(old('plan_id') == $p->id)>{{ $p->name }} ({{ number_format($p->price, 2) }} {{ $p->currency }}/{{ $p->billing_period }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                </div>
            </div>

            <h5 class="mt-4 mb-3 text-muted">Initial school admin</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Admin name <span class="text-danger">*</span></label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Admin email <span class="text-danger">*</span></label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Admin password <span class="text-danger">*</span></label>
                    <input type="text" name="admin_password" value="{{ old('admin_password', \Illuminate\Support\Str::random(12)) }}" class="form-control" required>
                </div>
            </div>

            <div class="alert alert-warning mt-4 mb-0 small">
                <i class="fas fa-exclamation-triangle me-1"></i>
                <strong>Before submitting:</strong> Create the MySQL database in cPanel and the subdomain (e.g. <code>lba.msitsols.com</code>) first. After creation, run <code>php artisan tenant:migrate --subdomain={{ '{subdomain}' }}</code> via SSH.
            </div>

            <div class="mt-4">
                <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Create school</button>
                <a href="{{ route('master.tenants.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
