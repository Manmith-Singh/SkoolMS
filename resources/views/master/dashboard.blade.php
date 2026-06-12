@extends('layouts.app')
@section('title', 'SuperAdmin Dashboard')

@section('content')
@php
    $stat = function (string $label, $value, string $color = 'var(--brand)', ?string $icon = null) {
        $iconHtml = $icon ? "<i class=\"$icon me-1\"></i>" : '';
        return '<div class="col-md-3">
            <div class="card stat-card p-3" style="border-left-color:'.$color.';">
                <div class="stat-label">'.$iconHtml.$label.'</div>
                <div class="stat-value">'.$value.'</div>
            </div>
        </div>';
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">SuperAdmin Dashboard</h3>
        <small class="text-muted">Welcome back, {{ auth()->user()->name }}. Here's the SaaS pulse.</small>
    </div>
    <a href="{{ route('master.tenants.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> New School
    </a>
</div>

<div class="row g-3 mb-4">
    {!! $stat('Schools',         $stats['tenants']) !!}
    {!! $stat('Active',          $stats['active'],         '#198754', 'fas fa-check-circle') !!}
    {!! $stat('Suspended',       $stats['suspended'],      '#dc3545', 'fas fa-pause-circle') !!}
    {!! $stat('Open Tickets',    $stats['open_tickets'],   '#ffc107', 'fas fa-life-ring') !!}
</div>

<div class="row g-3 mb-4">
    {!! $stat('Total Users',     $stats['users']) !!}
    {!! $stat('Super Admins',    $stats['super_admins'],   '#6f42c1', 'fas fa-user-shield') !!}
    {!! $stat('Overdue Invoices',$stats['overdue_invoices'],'#dc3545', 'fas fa-exclamation-triangle') !!}
    {!! $stat('MRR (USD)',       number_format($stats['mrr'], 2), '#198754', 'fas fa-dollar-sign') !!}
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Recent schools</strong>
                <a href="{{ route('master.tenants.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr><th>Name</th><th>Subdomain</th><th>Status</th><th>Created</th></tr>
                    </thead>
                    <tbody>
                    @forelse($recentTenants as $t)
                        <tr>
                            <td><a href="{{ route('master.tenants.show', $t) }}">{{ $t->name }}</a></td>
                            <td><a href="{{ $t->url() }}" target="_blank"><code>{{ $t->subdomain }}</code></a></td>
                            <td><span class="badge bg-{{ $t->status === 'active' ? 'success' : ($t->status === 'suspended' ? 'danger' : 'secondary') }}">{{ $t->status }}</span></td>
                            <td>{{ $t->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No schools yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Recent support tickets</strong>
                <a href="{{ route('master.tickets.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr><th>Subject</th><th>School</th><th>Priority</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    @forelse($recentTickets as $t)
                        <tr>
                            <td><a href="{{ route('master.tickets.show', $t) }}">{{ Str::limit($t->subject, 40) }}</a></td>
                            <td>{{ $t->tenant->name ?? '—' }}</td>
                            <td><span class="badge bg-{{ $t->priority === 'urgent' ? 'danger' : ($t->priority === 'high' ? 'warning' : 'secondary') }}">{{ $t->priority }}</span></td>
                            <td><span class="badge bg-info">{{ $t->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No tickets.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Recent invoices</strong>
                <a href="{{ route('master.invoices.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr><th>#</th><th>School</th><th>Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    @forelse($recentInvoices as $i)
                        <tr>
                            <td><a href="{{ route('master.invoices.show', $i) }}">{{ $i->invoice_number }}</a></td>
                            <td>{{ $i->tenant->name ?? '—' }}</td>
                            <td>{{ number_format($i->total, 2) }} {{ $i->currency }}</td>
                            <td><span class="badge bg-{{ $i->status === 'paid' ? 'success' : ($i->status === 'overdue' ? 'danger' : 'secondary') }}">{{ $i->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No invoices.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-white"><strong>Recent audit activity</strong></div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr><th>When</th><th>Actor</th><th>Action</th><th>Entity</th></tr>
            </thead>
            <tbody>
            @forelse($recentAudit as $a)
                <tr>
                    <td>{{ $a->created_at->diffForHumans() }}</td>
                    <td>{{ $a->user->name ?? 'system' }}</td>
                    <td><code>{{ $a->action }}</code></td>
                    <td class="text-muted small">{{ $a->entity_type ? class_basename($a->entity_type).'#'.$a->entity_id : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No activity yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
