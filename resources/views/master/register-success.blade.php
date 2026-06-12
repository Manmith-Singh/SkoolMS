@extends('layouts.auth')
@section('content')
<div class="text-center mb-3">
    <i class="fas fa-check-circle text-success" style="font-size:3rem;"></i>
    <h4 class="mt-2">School record created!</h4>
    <p class="text-muted">The admin user is ready. Before logging in, ensure your superadmin has completed the manual setup steps below.</p>
</div>

<table class="table table-sm">
    <tr><th>School</th><td>{{ $tenant->name }}</td></tr>
    <tr><th>Subdomain</th><td><a href="{{ $tenant->url() }}" target="_blank">{{ $tenant->subdomain }}.{{ config('tenancy.domain_base') }}</a></td></tr>
    <tr><th>Database</th><td><code>{{ $tenant->db_name }}</code></td></tr>
    <tr><th>Admin email</th><td>{{ $tenant->contact_email }}</td></tr>
    <tr><th>Admin password</th><td><code>{{ $password }}</code> <small class="text-muted">(save this, won't be shown again)</small></td></tr>
</table>

<div class="alert alert-info small">
    <strong><i class="fas fa-tasks me-1"></i> Manual setup required</strong>
    <ol class="mb-0 mt-2 ps-3">
        <li>Create MySQL database <code>{{ $tenant->db_name }}</code> in cPanel (if not done).</li>
        <li>Create subdomain <code>{{ $tenant->subdomain }}.{{ config('tenancy.app_domain') }}</code> in cPanel.</li>
        <li>Run via SSH: <code>php artisan tenant:migrate --subdomain={{ $tenant->subdomain }}</code></li>
        <li>Then log in at <a href="{{ $tenant->url() . '/login' }}">{{ $tenant->url() }}</a></li>
    </ol>
</div>
@endsection
