@extends('layouts.app')
@section('title', 'Edit student')

@section('content')
<h4 class="mb-3">Edit student</h4>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('students.update', $student) }}">
            @csrf @method('PUT')
            @include('students._form', ['student' => $student])
            <div class="mt-3">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
