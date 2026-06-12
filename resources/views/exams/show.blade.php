@extends('layouts.app')
@section('title', $exam->name)

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h4 class="mb-0">{{ $exam->name }}</h4>
        <small class="text-muted">
            @if($exam->classes->isNotEmpty())
                @foreach($exam->classes as $c)<span class="badge bg-secondary me-1">{{ $c->display_name }}</span>@endforeach
            @else
                —
            @endif
            · {{ $exam->subject->name }} · {{ $exam->date->format('d M Y') }}
        </small>
    </div>
    <a href="{{ route('exams.index') }}" class="btn btn-secondary">Back</a>
</div>
<div class="card p-3">
    <p><strong>Max marks:</strong> {{ $exam->max_marks }} · <strong>Pass marks:</strong> {{ $exam->pass_marks }}</p>
    <a href="{{ route('results.edit', $exam) }}" class="btn btn-success"><i class="fas fa-pen me-1"></i> Enter / edit marks</a>
</div>
@endsection
