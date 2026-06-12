@extends('layouts.app')
@section('title', $subject->name)

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">{{ $subject->name }}</h4>
    <a href="{{ route('subjects.index') }}" class="btn btn-secondary">Back</a>
</div>
<div class="card p-3">
    <table class="table mb-0">
        <tr><th>Code</th><td><code>{{ $subject->code ?? '—' }}</code></td></tr>
        <tr>
            <th>Class(es)</th>
            <td>
                @if($subject->classes->isNotEmpty())
                    @foreach($subject->classes as $c)
                        <span class="badge bg-secondary me-1">{{ $c->display_name }}</span>
                    @endforeach
                @else
                    —
                @endif
            </td>
        </tr>
        <tr><th>Description</th><td>{{ $subject->description ?? '—' }}</td></tr>
    </table>
</div>
@endsection
