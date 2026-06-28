@extends('layouts.app')
@section('title', $student->full_name)

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h4 class="mb-0">{{ $student->full_name }}</h4>
        <small class="text-muted">{{ $student->admission_no }} · {{ $student->schoolClass->display_name ?? '—' }}</small>
    </div>
    <div>
        <a href="{{ route('students.edit', $student) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i> Edit</a>
        <a href="{{ route('students.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="text-muted text-uppercase small">Personal</h6>
            <table class="table table-sm mb-0">
                <tr><th>DOB</th><td>{{ optional($student->dob)->format('d M Y') ?? '—' }}</td></tr>
                <tr><th>Gender</th><td>{{ ucfirst($student->gender ?? '—') }}</td></tr>
                <tr><th>Roll</th><td>{{ $student->roll_no ?? '—' }}</td></tr>
                <tr><th>Phone</th><td>{{ $student->phone ?? '—' }}</td></tr>
                <tr><th>Address</th><td>{{ $student->address ?? '—' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="text-muted text-uppercase small">Guardian</h6>
            <table class="table table-sm mb-0">
                <tr><th>Name</th><td>{{ $student->guardian_name ?? '—' }}</td></tr>
                <tr><th>Phone</th><td>{{ $student->guardian_phone ?? '—' }}</td></tr>
                <tr><th>Father</th><td>{{ $student->father_name ?? '—' }}</td></tr>
                <tr><th>Mother</th><td>{{ $student->mother_name ?? '—' }}</td></tr>
                <tr><th>PEN ID</th><td>{{ $student->pen_id ?? '—' }}</td></tr>
                <tr><th>Caste</th><td>{{ $student->caste ?? '—' }}</td></tr>
                <tr><th>Aadhaar</th><td>{{ $student->aadhaar_number ?? '—' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="text-muted text-uppercase small">Fee summary</h6>
            @php
                $total = $student->fees->sum('amount');
                $paid  = $student->fees->sum('paid_amount');
            @endphp
            <table class="table table-sm mb-0">
                <tr><th>Total fees</th><td>{{ number_format($total, 2) }}</td></tr>
                <tr><th>Paid</th><td class="text-success">{{ number_format($paid, 2) }}</td></tr>
                <tr><th>Balance</th><td class="text-danger">{{ number_format($total - $paid, 2) }}</td></tr>
            </table>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white"><strong>Recent attendance</strong></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Date</th><th>Status</th><th>Remarks</th></tr></thead>
                    <tbody>
                    @forelse($student->attendance->take(15) as $a)
                        <tr>
                            <td>{{ $a->date->format('d M Y') }}</td>
                            <td><span class="badge badge-status-{{ $a->status }}">{{ ucfirst(str_replace('_',' ', $a->status)) }}</span></td>
                            <td>{{ $a->remarks ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No attendance records.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
