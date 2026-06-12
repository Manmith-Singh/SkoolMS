@extends('layouts.app')
@section('title', 'Attendance report')

@section('content')
<h4 class="mb-3">Attendance report — {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</h4>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="row g-2">
        <div class="col-md-3"><input type="month" name="month" value="{{ $month }}" class="form-control"></div>
        <div class="col-md-3">
            <select name="class_id" class="form-select">
                <option value="">All classes</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" @selected($classId == $c->id)>{{ $c->display_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i> Apply</button></div>
    </form>
</div></div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr>
                    <th>Adm</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Half day</th>
                    <th>Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                @php
                    $p = $s->attendance->where('status', 'present')->count();
                    $a = $s->attendance->where('status', 'absent')->count();
                    $l = $s->attendance->where('status', 'late')->count();
                    $h = $s->attendance->where('status', 'half_day')->count();
                    $total = $p + $a + $l + $h;
                    $rate = $total > 0 ? round((($p + $l*0.5 + $h*0.5) / $total) * 100, 1) : null;
                @endphp
                <tr>
                    <td><code>{{ $s->admission_no }}</code></td>
                    <td>{{ $s->full_name }}</td>
                    <td>{{ $s->schoolClass->display_name ?? '—' }}</td>
                    <td class="text-success">{{ $p }}</td>
                    <td class="text-danger">{{ $a }}</td>
                    <td class="text-warning">{{ $l }}</td>
                    <td>{{ $h }}</td>
                    <td><strong>{{ $rate !== null ? $rate.'%' : '—' }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
