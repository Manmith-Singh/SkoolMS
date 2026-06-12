@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Welcome back, {{ auth()->user()->name }}!</h3>
    <small class="text-muted">{{ now()->format('l, d F Y') }}</small>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-label">Students</div>
                    <div class="stat-value">{{ number_format($stats['students']) }}</div>
                </div>
                <i class="fas fa-user-graduate fa-2x text-muted"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card p-3" style="border-left-color:#198754;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-label">Teachers</div>
                    <div class="stat-value text-success">{{ number_format($stats['teachers']) }}</div>
                </div>
                <i class="fas fa-user-tie fa-2x text-muted"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card p-3" style="border-left-color:#fd7e14;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-label">Classes</div>
                    <div class="stat-value text-warning">{{ number_format($stats['classes']) }}</div>
                </div>
                <i class="fas fa-chalkboard fa-2x text-muted"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card p-3" style="border-left-color:#6f42c1;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-label">Subjects</div>
                    <div class="stat-value text-purple">{{ number_format($stats['subjects']) }}</div>
                </div>
                <i class="fas fa-book fa-2x text-muted"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card p-3" style="border-left-color:#20c997;">
            <div class="stat-label">Today Present</div>
            <div class="stat-value" style="color:#20c997;">{{ $stats['today_present'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card p-3" style="border-left-color:#dc3545;">
            <div class="stat-label">Today Absent</div>
            <div class="stat-value text-danger">{{ $stats['today_absent'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card p-3" style="border-left-color:#0d6efd;">
            <div class="stat-label">Fees collected</div>
            <div class="stat-value" style="color:#0d6efd;">{{ number_format($stats['fees_collected'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card p-3" style="border-left-color:#dc3545;">
            <div class="stat-label">Fees pending</div>
            <div class="stat-value text-danger">{{ number_format($stats['fees_pending'], 2) }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white">
                <strong>Fee collection — last 6 months</strong>
            </div>
            <div class="card-body">
                <canvas id="feesChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between">
                <strong>Recent payments</strong>
                <a href="{{ route('fees.payments.index') }}" class="text-decoration-none small">View all</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($recentPayments as $p)
                        <li class="list-group-item d-flex justify-content-between">
                            <div>
                                <strong>{{ $p->student->full_name ?? '—' }}</strong><br>
                                <small class="text-muted">{{ $p->fee->category->name ?? '' }} · {{ ucfirst($p->mode) }}</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success">{{ number_format($p->amount_paid, 2) }}</div>
                                <small class="text-muted">{{ $p->payment_date->format('d M') }}</small>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-4">No payments yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between">
                <strong>Recently admitted students</strong>
                <a href="{{ route('students.index') }}" class="text-decoration-none small">View all</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Adm No</th><th>Name</th><th>Class</th><th>Joined</th></tr></thead>
                    <tbody>
                    @forelse($recentStudents as $s)
                        <tr>
                            <td>{{ $s->admission_no }}</td>
                            <td><a href="{{ route('students.show', $s) }}">{{ $s->full_name }}</a></td>
                            <td>{{ $s->schoolClass->display_name ?? '—' }}</td>
                            <td>{{ optional($s->admission_date)->format('d M Y') ?? $s->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No students yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const ctx = document.getElementById('feesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($months),
            datasets: [{
                label: 'Collected',
                data: @json($collected),
                borderColor: '#3b6db5',
                backgroundColor: 'rgba(59,109,181,.15)',
                fill: true,
                tension: 0.35,
            }]
        },
        options: {
            plugins: { legend: { display: false }},
            scales: { y: { beginAtZero: true }}
        }
    });
</script>
@endpush
