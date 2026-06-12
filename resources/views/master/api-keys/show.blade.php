@extends('layouts.app')
@section('title', $apiKey->name)

@section('content')
@if($newSecret)
    <div class="alert alert-warning">
        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-1"></i> Save your secret now</h5>
        <p class="mb-2 small">For security, this is the <strong>only time</strong> the secret will be shown. Copy it somewhere safe.</p>
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label small mb-1">Public key</label>
                <input type="text" class="form-control" readonly value="{{ $newKey }}" onclick="this.select()">
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-1">Secret</label>
                <input type="text" class="form-control" readonly value="{{ $newSecret }}" onclick="this.select()">
            </div>
        </div>
    </div>
@endif

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h3 class="mb-0">{{ $apiKey->name }}</h3>
        <small class="text-muted"><code>{{ $apiKey->key }}</code></small>
    </div>
    <a href="{{ route('master.api-keys.index') }}" class="btn btn-link">Back</a>
</div>

<div class="row g-3">
    <div class="col-md-4"><div class="card stat-card p-3"><div class="stat-label">Status</div><div class="stat-value"><span class="badge bg-{{ $apiKey->is_active ? 'success' : 'secondary' }}">{{ $apiKey->is_active ? 'active' : 'revoked' }}</span></div></div></div>
    <div class="col-md-4"><div class="card stat-card p-3"><div class="stat-label">Last used</div><div class="stat-value">{{ $apiKey->last_used_at?->diffForHumans() ?? 'never' }}</div></div></div>
    <div class="col-md-4"><div class="card stat-card p-3"><div class="stat-label">Expires</div><div class="stat-value">{{ $apiKey->expires_at?->format('Y-m-d') ?? '—' }}</div></div></div>
</div>

<div class="card mt-3">
    <div class="card-header bg-white"><strong>Scopes</strong></div>
    <div class="card-body">
        @if($apiKey->scopes)
            @foreach($apiKey->scopes as $s)
                <span class="badge bg-light text-dark border me-1 mb-1">{{ $s }}</span>
            @endforeach
        @else
            <span class="text-muted">All scopes (full access)</span>
        @endif
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-white"><strong>Audit</strong></div>
    <div class="card-body small">
        <div>Created by <strong>{{ $apiKey->creator->name ?? 'system' }}</strong> on {{ $apiKey->created_at->format('Y-m-d H:i') }}</div>
    </div>
</div>
@endsection
