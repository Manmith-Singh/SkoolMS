@extends('layouts.app')
@section('title', 'New API key')

@section('content')
<h3 class="mb-4">New API key</h3>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('master.api-keys.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required placeholder="Zapier integration, mobile app, ...">
        </div>
        <div class="col-md-3">
            <label class="form-label">TTL (days)</label>
            <input type="number" name="ttl_days" value="{{ old('ttl_days', 365) }}" min="1" max="3650" class="form-control">
        </div>
        <div class="col-12">
            <label class="form-label">Scopes (comma or space separated)</label>
            <input type="text" name="scopes" value="{{ old('scopes') }}" class="form-control" placeholder="tenants:read, invoices:write, reports:read">
            <div class="form-text">Leave blank for full access (not recommended).</div>
        </div>
    </div>
    <div class="mt-4">
        <button class="btn btn-primary"><i class="fas fa-key me-1"></i>Generate key</button>
        <a href="{{ route('master.api-keys.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>
@endsection
