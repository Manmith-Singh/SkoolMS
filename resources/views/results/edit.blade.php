@extends('layouts.app')
@section('title', 'Enter marks')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h4 class="mb-0">Enter marks: {{ $exam->name }}</h4>
        <small class="text-muted">
            @if($exam->classes->isNotEmpty())
                @foreach($exam->classes as $c)<span class="badge bg-secondary me-1">{{ $c->display_name }}</span>@endforeach
            @else
                —
            @endif
            · {{ $exam->subject->name }} · Max {{ $exam->max_marks }}
        </small>
    </div>
    <a href="{{ route('exams.index') }}" class="btn btn-secondary">Back</a>
</div>

<form method="POST" action="{{ route('results.update', $exam) }}">
    @csrf @method('PUT')
    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Adm No</th>
                        <th>Student</th>
                        <th style="width:160px;">Marks (out of {{ $exam->max_marks }})</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($exam->results as $r)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><code>{{ $r->student->admission_no }}</code></td>
                        <td>{{ $r->student->full_name }} <small class="text-muted">({{ $r->student->roll_no ?? '—' }})</small></td>
                        <td>
                            <input type="number" step="0.01" min="0" max="{{ $exam->max_marks }}"
                                   name="marks[{{ $r->id }}]" value="{{ $r->marks_obtained }}" class="form-control" required>
                        </td>
                        <td>
                            <input type="text" name="remarks[{{ $r->id }}]" value="{{ $r->remarks }}" class="form-control">
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-success"><i class="fas fa-save me-1"></i> Save all marks</button>
        </div>
    </div>
</form>
@endsection
