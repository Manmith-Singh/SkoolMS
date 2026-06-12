@extends('layouts.app')
@section('title', 'Audit log')

@section('content')
<h3 class="mb-4">Audit log</h3>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Action contains</label>
                <input type="text" name="action" value="{{ request('action') }}" class="form-control" placeholder="tenant.suspended, payment.recorded, ...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Entity</th><th>IP</th><th>Metadata</th></tr></thead>
            <tbody>
            @forelse($logs as $l)
                <tr>
                    <td>{{ $l->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $l->user->name ?? 'system' }}</td>
                    <td><code>{{ $l->action }}</code></td>
                    <td>{{ $l->entity_type ? class_basename($l->entity_type).'#'.$l->entity_id : '—' }}</td>
                    <td><code class="small">{{ $l->ip_address ?? '—' }}</code></td>
                    <td><pre class="small mb-0 text-muted" style="max-width: 360px; white-space: pre-wrap;">{{ $l->metadata ? json_encode($l->metadata, JSON_PRETTY_PRINT) : '—' }}</pre></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No audit entries.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $logs->links() }}</div>
</div>
@endsection
