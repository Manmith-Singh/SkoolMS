@extends('layouts.app')
@section('title', 'Support tickets')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Support tickets</h3>
    <a href="{{ route('master.tickets.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New ticket</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach(['open', 'in_progress', 'waiting', 'resolved', 'closed'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All</option>
                    @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                        <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">School</label>
                <select name="tenant_id" class="form-select">
                    <option value="">All</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" @selected(request('tenant_id') == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0 datatable">
            <thead><tr><th>#</th><th>Subject</th><th>School</th><th>Priority</th><th>Status</th><th>Last reply</th></tr></thead>
            <tbody>
            @foreach($tickets as $t)
                <tr>
                    <td><a href="{{ route('master.tickets.show', $t) }}">#{{ $t->id }}</a></td>
                    <td>{{ Str::limit($t->subject, 50) }}</td>
                    <td>{{ $t->tenant->name ?? '—' }}</td>
                    <td><span class="badge bg-{{ $t->priority === 'urgent' ? 'danger' : ($t->priority === 'high' ? 'warning' : 'secondary') }}">{{ $t->priority }}</span></td>
                    <td><span class="badge bg-info">{{ str_replace('_', ' ', $t->status) }}</span></td>
                    <td>{{ $t->last_reply_at?->diffForHumans() ?? $t->created_at->diffForHumans() }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $tickets->links() }}</div>
</div>
@endsection
