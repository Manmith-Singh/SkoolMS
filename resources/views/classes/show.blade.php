@extends('layouts.app')
@section('title', $class->display_name)

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">{{ $class->display_name }}</h4>
    <a href="{{ route('classes.index') }}" class="btn btn-secondary">Back</a>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="text-muted small text-uppercase">Subjects ({{ $class->subjects->count() }})</h6>
            <ul class="list-unstyled mb-0">
                @foreach($class->subjects as $s)
                    <li><i class="fas fa-book text-muted me-1"></i> {{ $s->name }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">Students ({{ $class->students->count() }})</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Adm</th><th>Name</th><th>Roll</th></tr></thead>
                    <tbody>
                    @foreach($class->students as $s)
                        <tr>
                            <td><code>{{ $s->admission_no }}</code></td>
                            <td>{{ $s->full_name }}</td>
                            <td>{{ $s->roll_no ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
