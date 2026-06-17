@extends('layouts.app')
@section('title', $exam->name)

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h4 class="mb-0">{{ $exam->name }}</h4>
        <small class="text-muted">
            @if($exam->examType)<span class="badge bg-primary me-1">{{ $exam->examType->name }}</span>@endif
            @if($exam->classes->isNotEmpty())
                @foreach($exam->classes as $c)<span class="badge bg-secondary me-1">{{ $c->display_name }}</span>@endforeach
            @endif
            @if($exam->from_date)
                · {{ $exam->from_date->format('d M') }} – {{ $exam->to_date->format('d M Y') }}
            @endif
        </small>
    </div>
    <a href="{{ route('exams.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="card p-3 mb-3">
    <div class="row">
        <div class="col-md-3"><strong>Max marks:</strong> {{ $exam->max_marks }}</div>
        <div class="col-md-3"><strong>Pass marks:</strong> {{ $exam->pass_marks }}</div>
        <div class="col-md-6"><strong>Description:</strong> {{ $exam->description ?? '—' }}</div>
    </div>
</div>

@if($exam->subjects->isNotEmpty())
    <div class="card mb-3">
        <div class="card-header"><strong>Subject schedule</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr><th>#</th><th>Subject</th><th>Date</th><th>Notes / Portion</th></tr>
                </thead>
                <tbody>
                    @foreach($exam->subjects->sortBy('pivot.order') as $s)
                        <tr>
                            <td>{{ $s->pivot->order }}</td>
                            <td>{{ $s->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($s->pivot->date)->format('d M Y') }}</td>
                            <td>{{ $s->pivot->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<a href="{{ route('results.edit', $exam) }}" class="btn btn-success"><i class="fas fa-pen me-1"></i> Enter / edit marks</a>
@endsection
