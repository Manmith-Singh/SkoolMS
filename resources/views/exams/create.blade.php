@extends('layouts.app')
@section('title', 'Add exam')

@section('content')
<h4 class="mb-3">Schedule exam</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('exams.store') }}">
        @csrf
        @include('exams._form', ['exam' => null])
        <div class="mt-3">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('exams.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
