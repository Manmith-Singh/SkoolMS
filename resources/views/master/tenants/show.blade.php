@extends('layouts.app')
@section('title', $tenant->name)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h3 class="mb-0">{{ $tenant->name }}
            <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : ($tenant->status === 'suspended' ? 'danger' : 'secondary') }} ms-2">{{ $tenant->status }}</span>
        </h3>
        <small class="text-muted"><a href="{{ $tenant->url() }}" target="_blank"><code>{{ $tenant->subdomain }}.{{ config('tenancy.app_domain') }}</code></a> &middot; DB <code>{{ $tenant->db_name }}</code></small>
    </div>
    <div class="btn-group">
        <a href="{{ $tenant->url() }}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-external-link-alt me-1"></i>Open</a>
        <a href="{{ route('master.tenants.edit', $tenant) }}" class="btn btn-outline-secondary"><i class="fas fa-edit me-1"></i>Edit</a>
        <a href="{{ route('master.tenants.academic-years', $tenant) }}" class="btn btn-outline-info"><i class="fas fa-calendar-alt me-1"></i>Academic Years</a>
        @if($tenant->status === 'active')
            <form method="POST" action="{{ route('master.tenants.suspend', $tenant) }}" class="d-inline">
                @csrf
                <button class="btn btn-outline-warning" onclick="return confirm('Suspend this school?')"><i class="fas fa-pause me-1"></i>Suspend</button>
            </form>
        @else
            <form method="POST" action="{{ route('master.tenants.activate', $tenant) }}" class="d-inline">
                @csrf
                <button class="btn btn-outline-success"><i class="fas fa-play me-1"></i>Activate</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card stat-card p-3"><div class="stat-label">Users</div><div class="stat-value">{{ $stats['users'] }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3" style="border-left-color:#0d6efd;"><div class="stat-label">Invoices</div><div class="stat-value">{{ $stats['invoices'] }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3" style="border-left-color:#198754;"><div class="stat-label">Total paid</div><div class="stat-value text-success">{{ number_format($stats['paid'], 2) }}</div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3" style="border-left-color:#ffc107;"><div class="stat-label">Tickets</div><div class="stat-value">{{ $stats['tickets'] }}</div></div></div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white"><strong>Recent invoices</strong></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>#</th><th>Total</th><th>Status</th><th>Issued</th></tr></thead>
                    <tbody>
                    @forelse($recentInvoices as $i)
                        <tr>
                            <td><a href="{{ route('master.invoices.show', $i) }}">{{ $i->invoice_number }}</a></td>
                            <td>{{ number_format($i->total, 2) }} {{ $i->currency }}</td>
                            <td><span class="badge bg-{{ $i->status === 'paid' ? 'success' : 'secondary' }}">{{ $i->status }}</span></td>
                            <td>{{ $i->issue_date->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No invoices.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Recent payments</strong></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Amount</th><th>Method</th><th>Reference</th><th>Paid at</th></tr></thead>
                    <tbody>
                    @forelse($recentPayments as $p)
                        <tr>
                            <td>{{ number_format($p->amount, 2) }} {{ $p->currency }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $p->method)) }}</td>
                            <td><code>{{ $p->reference ?? '—' }}</code></td>
                            <td>{{ $p->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No payments.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-white"><strong>Recent tickets</strong></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Subject</th><th>Status</th><th>Priority</th></tr></thead>
                    <tbody>
                    @forelse($recentTickets as $t)
                        <tr>
                            <td><a href="{{ route('master.tickets.show', $t) }}">{{ Str::limit($t->subject, 50) }}</a></td>
                            <td><span class="badge bg-info">{{ $t->status }}</span></td>
                            <td><span class="badge bg-{{ $t->priority === 'urgent' ? 'danger' : ($t->priority === 'high' ? 'warning' : 'secondary') }}">{{ $t->priority }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">No tickets.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
