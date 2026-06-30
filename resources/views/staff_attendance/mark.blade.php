@extends('layouts.app')
@section('title', 'Mark Staff Attendance')

@section('content')
<h4 class="mb-3">Mark Staff Attendance</h4>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Date</label>
            <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="form-control" id="attendance-date">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100"><i class="fas fa-search"></i> Load</button>
        </div>
    </form>
</div></div>

@if(request('date') && $teachers->isNotEmpty())
<form method="POST" action="{{ route('staff-attendance.store') }}">
    @csrf
    <input type="hidden" name="date" value="{{ request('date') }}">

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Teachers for {{ \Carbon\Carbon::parse(request('date'))->format('d M Y') }}</strong>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-success" id="btn-all-present"><i class="fas fa-check me-1"></i>All Present</button>
                <button type="button" class="btn btn-outline-danger" id="btn-all-absent"><i class="fas fa-times me-1"></i>All Absent</button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>#</th><th>Teacher</th><th>Status</th><th>Remarks</th></tr></thead>
                <tbody>
                    @foreach($teachers as $i => $teacher)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            {{ $teacher->full_name ?? $teacher->name }}
                            <input type="hidden" name="teacher_ids[]" value="{{ $teacher->id }}">
                        </td>
                        <td>
                            <select name="statuses[{{ $teacher->id }}]" class="form-select form-select-sm status-select" style="width:auto;">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="half_day">Half day</option>
                                <option value="leave">Leave</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="remarks[{{ $teacher->id }}]" class="form-control form-control-sm" placeholder="Optional remarks" maxlength="255">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Attendance</button>
            <a href="{{ route('staff-attendance.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>
@elseif(request('date'))
<div class="alert alert-info">No teachers found.</div>
@endif
@endsection

@push('scripts')
<script>
document.getElementById('btn-all-present')?.addEventListener('click', function() {
    document.querySelectorAll('.status-select').forEach(s => s.value = 'present');
});
document.getElementById('btn-all-absent')?.addEventListener('click', function() {
    document.querySelectorAll('.status-select').forEach(s => s.value = 'absent');
});
</script>
@endpush
