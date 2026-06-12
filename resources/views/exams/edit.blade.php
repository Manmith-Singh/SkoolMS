@extends('layouts.app')
@section('title', 'Edit exam')

@section('content')
<h4 class="mb-3">Edit exam</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('exams.update', $exam) }}">
        @csrf @method('PUT')
        @include('exams._form', ['exam' => $exam])
        <div class="mt-3">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('exams.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
