@extends('layouts.app')
@section('title', 'Edit teacher')

@section('content')
<h4 class="mb-3">Edit teacher</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('teachers.update', $teacher) }}">
        @csrf @method('PUT')
        @include('teachers._form', ['teacher' => $teacher])
        <div class="mt-3">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
