@extends('layouts.app')
@section('title', $teacher->full_name)

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">{{ $teacher->full_name }}</h4>
    <a href="{{ route('teachers.edit', $teacher) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i> Edit</a>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100"><div class="card-body">
            <h6 class="card-title">Basic Info</h6>
            <table class="table mb-0">
                <tr><th>Employee ID</th><td>{{ $teacher->employee_id }}</td></tr>
                <tr><th>Email</th><td>{{ $teacher->email }}</td></tr>
                <tr><th>Phone</th><td>{{ $teacher->phone ?? '—' }}</td></tr>
                <tr><th>Gender</th><td>{{ ucfirst($teacher->gender ?? '—') }}</td></tr>
                <tr><th>Qualification</th><td>{{ $teacher->qualification ?? '—' }}</td></tr>
                <tr><th>Hire date</th><td>{{ optional($teacher->hire_date)->format('d M Y') ?? '—' }}</td></tr>
                <tr><th>Status</th><td>{!! $teacher->status ? '<span class="badge bg-'.($teacher->status === 'working' ? 'success' : ($teacher->status === 'resigned' ? 'secondary' : 'warning')).'">'.ucfirst($teacher->status).'</span>' : '—' !!}</td></tr>
                <tr><th>Address</th><td>{{ $teacher->address ?? '—' }}</td></tr>
            </table>
        </div></div>
    </div>

    <div class="col-md-6">
        <div class="card h-100"><div class="card-body">
            <h6 class="card-title">Subjects & Classes</h6>
            <table class="table mb-0">
                <tr><th>Subject(s)</th><td>
                    @if($teacher->subjects->isNotEmpty())
                        @foreach($teacher->subjects as $s)<span class="badge bg-info me-1">{{ $s->name }}</span>@endforeach
                    @else
                        {{ $teacher->subject->name ?? '—' }}
                    @endif
                </td></tr>
                <tr><th>Class Teacher</th><td>{{ $teacher->classTeacher->display_name ?? '—' }}</td></tr>
            </table>
        </div></div>
    </div>

    <div class="col-md-6">
        <div class="card h-100"><div class="card-body">
            <h6 class="card-title">Payroll / Bank Details</h6>
            <table class="table mb-0">
                <tr><th>Salary</th><td>{{ number_format($teacher->salary ?? 0, 2) }}</td></tr>
                <tr><th>Basic Pay</th><td>{{ number_format($teacher->basic_pay ?? 0, 2) }}</td></tr>
                <tr><th>HRA</th><td>{{ number_format($teacher->hra ?? 0, 2) }}</td></tr>
                <tr><th>DA</th><td>{{ number_format($teacher->da ?? 0, 2) }}</td></tr>
                <tr><th>Conveyance</th><td>{{ number_format($teacher->conveyance ?? 0, 2) }}</td></tr>
                <tr><th>Medical Allowance</th><td>{{ number_format($teacher->medical_allowance ?? 0, 2) }}</td></tr>
                <tr><th>Other Allowances</th><td>{{ number_format($teacher->other_allowances ?? 0, 2) }}</td></tr>
                <tr><th>PF Number</th><td>{{ $teacher->pf_number ?? '—' }}</td></tr>
                <tr><th>ESI Number</th><td>{{ $teacher->esi_number ?? '—' }}</td></tr>
                <tr><th>UAN</th><td>{{ $teacher->uan_number ?? '—' }}</td></tr>
                <tr><th>Bank Account</th><td>{{ $teacher->bank_account ?? '—' }}</td></tr>
                <tr><th>IFSC</th><td>{{ $teacher->ifsc_code ?? '—' }}</td></tr>
            </table>
        </div></div>
    </div>

    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="card-title mb-0">Increment History</h6>
                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addIncrement"><i class="fas fa-plus"></i> Add</button>
            </div>
            @if($teacher->increments->isNotEmpty())
                <table class="table mb-0">
                    <thead><tr><th>Date</th><th>Amount</th><th>Reason</th><th></th></tr></thead>
                    <tbody>
                        @foreach($teacher->increments as $inc)
                        <tr>
                            <td>{{ $inc->effective_date->format('d M Y') }}</td>
                            <td class="text-success">+{{ number_format($inc->amount, 2) }}</td>
                            <td>{{ $inc->reason ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('teacher-increments.destroy', $inc) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted small mb-0">No increments recorded.</p>
            @endif
        </div></div>
    </div>
</div>

<div class="modal fade" id="addIncrement" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('teacher-increments.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
            <div class="modal-header">
                <h5 class="modal-title">Add Increment</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Amount *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Effective date *</label>
                    <input type="date" name="effective_date" value="{{ today()->toDateString() }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <textarea name="reason" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
