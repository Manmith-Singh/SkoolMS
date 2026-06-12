@extends('layouts.app')
@section('title', 'API Keys')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">API Keys</h3>
    <a href="{{ route('master.api-keys.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New key</a>
</div>

<div class="alert alert-warning small">
    <i class="fas fa-exclamation-triangle me-1"></i>
    API keys grant programmatic access to the SaaS. Only create them for trusted integrations.
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0 datatable">
            <thead><tr><th>Name</th><th>Key</th><th>Scopes</th><th>Status</th><th>Last used</th><th>Expires</th><th></th></tr></thead>
            <tbody>
            @foreach($keys as $k)
                <tr>
                    <td><a href="{{ route('master.api-keys.show', $k) }}">{{ $k->name }}</a></td>
                    <td><code>{{ Str::limit($k->key, 24) }}</code></td>
                    <td>
                        @if($k->scopes)
                            @foreach($k->scopes as $s)
                                <span class="badge bg-light text-dark border">{{ $s }}</span>
                            @endforeach
                        @else
                            <span class="text-muted small">all</span>
                        @endif
                    </td>
                    <td><span class="badge bg-{{ $k->is_active ? 'success' : 'secondary' }}">{{ $k->is_active ? 'active' : 'revoked' }}</span></td>
                    <td>{{ $k->last_used_at?->diffForHumans() ?? 'never' }}</td>
                    <td>{{ $k->expires_at?->format('Y-m-d') ?? '—' }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('master.api-keys.destroy', $k) }}" onsubmit="return confirm('Revoke this key? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-ban"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
