@extends('layouts.app')
@section('title', 'Results')

@section('content')
<h4 class="mb-3">All results</h4>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <select name="exam_id" class="form-select">
                    <option value="">All exams</option>
                    @foreach($exams as $e)
                        <option value="{{ $e->id }}" @selected(request('exam_id') == $e->id)>{{ $e->name }} — {{ $e->date->format('d M Y') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="student_id" class="form-select">
                    <option value="">All students</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" @selected(request('student_id') == $s->id)>{{ $s->full_name }} ({{ $s->admission_no }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr><th>Exam</th><th>Class</th><th>Subject</th><th>Student</th><th>Marks</th><th>%</th><th>Grade</th><th>Result</th></tr>
            </thead>
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
                    <td>{{ $r->exam->subject->name ?? '—' }}</td>
                    <td>{{ $r->student->full_name ?? '—' }}</td>
                    <td>{{ $r->marks_obtained }} / {{ $r->exam->max_marks }}</td>
                    <td>{{ $r->percentage() }}</td>
                    <td><strong>{{ $r->grade }}</strong></td>
                    <td>
                        <span class="badge {{ $r->isPass() ? 'bg-success' : 'bg-danger' }}">
                            {{ $r->isPass() ? 'Pass' : 'Fail' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $results->links() }}</div>
</div>
@endsection
