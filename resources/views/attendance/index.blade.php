@extends('layouts.app')
@section('title', 'Attendance')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Attendance</h4>
    <a href="{{ route('attendance.mark') }}" class="btn btn-primary"><i class="fas fa-check me-1"></i> Mark attendance</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <input type="date" name="date" value="{{ request('date') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <select name="class_id" class="form-select">
                    <option value="">All classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" @selected(request('class_id') == $c->id)>{{ $c->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach(['present','absent','late','half_day'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ', $s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i> Filter</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr><th>Date</th><th>Student</th><th>Class</th><th>Status</th><th>Remarks</th></tr>
            </thead>
            <tbody>
                @foreach($records as $a)
                <tr>
                    <td>{{ $a->date->format('d M Y') }}</td>
                    <td>{{ $a->student->full_name ?? '—' }}</td>
                    <td>{{ $a->schoolClass->display_name ?? '—' }}</td>
                    <td><span class="badge badge-status-{{ $a->status }}">{{ ucfirst(str_replace('_',' ', $a->status)) }}</span></td>
                    <td>{{ $a->remarks ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $records->links() }}</div>
</div>
@endsection
