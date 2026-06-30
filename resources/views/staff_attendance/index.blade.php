@extends('layouts.app')
@section('title', 'Staff Attendance')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Staff Attendance</h4>
    <a href="{{ route('staff-attendance.mark') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Mark attendance</a>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Date</label>
            <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Status</label>
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                <option value="present" @selected(request('status') == 'present')>Present</option>
                <option value="absent" @selected(request('status') == 'absent')>Absent</option>
                <option value="late" @selected(request('status') == 'late')>Late</option>
                <option value="half_day" @selected(request('status') == 'half_day')>Half day</option>
                <option value="leave" @selected(request('status') == 'leave')>Leave</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i> Filter</button>
        </div>
    </form>
</div></div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr><th>Date</th><th>Teacher</th><th>Status</th><th>Remarks</th></tr>
            </thead>
            <tbody>
                @foreach($records as $r)
                <tr>
                    <td>{{ $r->date?->format('d M Y') ?? '—' }}</td>
                    <td>{{ $r->teacher->full_name ?? $r->teacher->name ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $r->status == 'present' ? 'success' : ($r->status == 'absent' ? 'danger' : ($r->status == 'late' ? 'warning' : ($r->status == 'half_day' ? 'info' : 'secondary'))) }}">
                            {{ ucfirst(str_replace('_', ' ', $r->status)) }}
                        </span>
                    </td>
                    <td>{{ $r->remarks ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(method_exists($records, 'links'))
    <div class="card-footer">
        {{ $records->links() }}
    </div>
    @endif
</div>
@endsection
