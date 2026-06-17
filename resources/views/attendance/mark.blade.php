@extends('layouts.app')
@section('title', 'Mark attendance')

@section('content')
<h4 class="mb-3">Mark attendance</h4>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            @include('partials._class_section_fields', [
                'name'       => 'class_id',
                'classes'    => $classes,
                'selected'   => (array) (request('class_id') ?? []),
                'hideLabels' => true,
            ])
        </div>
        <div class="col-md-3">
            <label class="form-label small">Date</label>
            <input type="date" name="date" value="{{ $date }}" class="form-control">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100">Load</button>
        </div>
    </form>
</div></div>

@if($selectedClass && $students->isNotEmpty())
<form method="POST" action="{{ route('attendance.store') }}">
    @csrf
    <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
    <input type="hidden" name="date" value="{{ $date }}">

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between">
            <strong>{{ $selectedClass->display_name }}{{ count(request('class_id', [])) > 1 ? ' (+' . (count(request('class_id', [])) - 1) . ' more)' : '' }} — {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</strong>
            <div>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="setAll('present')">All present</button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="setAll('absent')">All absent</button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>#</th><th>Roll</th><th>Student</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($students as $s)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $s->roll_no ?? '—' }}</td>
                        <td>{{ $s->full_name }}</td>
                        <td>
                            <select name="attendances[{{ $s->id }}]" class="form-select form-select-sm">
                                @foreach(['present','absent','late','half_day'] as $st)
                                    <option value="{{ $st }}" @selected($st === 'present')>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-success"><i class="fas fa-save me-1"></i> Save attendance</button>
        </div>
    </div>
</form>
@elseif($selectedClass)
    <div class="alert alert-info">No students in this class yet.</div>
@else
    <div class="alert alert-info">Pick a class to start marking.</div>
@endif

<script>
function setAll(value) {
    document.querySelectorAll('select[name^="attendances"]').forEach(s => s.value = value);
}
</script>
@endsection
