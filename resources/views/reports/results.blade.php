@extends('layouts.app')
@section('title', 'Results report')

@section('content')
<h4 class="mb-3">Exam results report</h4>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card p-3 stat-card"><div class="stat-label">Total entries</div><div class="stat-value">{{ $summary['total'] }}</div></div></div>
    <div class="col-md-3"><div class="card p-3 stat-card" style="border-left-color:#198754;"><div class="stat-label">Pass</div><div class="stat-value text-success">{{ $summary['pass'] }}</div></div></div>
    <div class="col-md-3"><div class="card p-3 stat-card" style="border-left-color:#dc3545;"><div class="stat-label">Fail</div><div class="stat-value text-danger">{{ $summary['fail'] }}</div></div></div>
    <div class="col-md-3"><div class="card p-3 stat-card" style="border-left-color:#0d6efd;"><div class="stat-label">Avg %</div><div class="stat-value">{{ number_format($summary['avg_pct'], 2) }}</div></div></div>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="row g-2">
        <div class="col-md-3">
            @include('partials._class_section_fields', [
                'name'     => 'class_id',
                'classes'  => $classes,
                'selected' => (array) (request('class_id') ?? []),
            ])
        </div>
        <div class="col-md-3">
            <select name="exam_id" class="form-select">
                <option value="">All exams</option>
                @foreach($exams as $e)
                    <option value="{{ $e->id }}" @selected(request('exam_id') == $e->id)>{{ $e->name }} ({{ $e->from_date?->format('d M Y') ?? '' }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i> Filter</button></div>
    </form>
</div></div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead><tr><th>Exam</th><th>Class</th><th>Subject</th><th>Student</th><th>Marks</th><th>%</th><th>Grade</th><th>Result</th></tr></thead>
            <tbody>
                @foreach($results as $r)
                <tr>
                    <td>{{ $r->exam->name }}</td>
                    <td>
                        @if($r->exam->classes->isNotEmpty())
                            @foreach($r->exam->classes as $c)<span class="badge bg-secondary me-1">{{ $c->display_name }}</span>@endforeach
                        @else
                            —
                        @endif
                    </td>
                    <td>
    @if($r->exam->subjects->isNotEmpty())
        @foreach($r->exam->subjects as $s)<span class="badge bg-info me-1">{{ $s->name }}</span>@endforeach
    @else
        —
    @endif
</td>
                    <td>{{ $r->student->full_name ?? '—' }}</td>
                    <td>{{ $r->marks_obtained }} / {{ $r->exam->max_marks }}</td>
                    <td>{{ $r->percentage() }}%</td>
                    <td><strong>{{ $r->grade }}</strong></td>
                    <td><span class="badge bg-{{ $r->isPass() ? 'success' : 'danger' }}">{{ $r->isPass() ? 'Pass' : 'Fail' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
