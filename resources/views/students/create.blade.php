@extends('layouts.app')
@section('title', 'Add student')

@section('content')
<h4 class="mb-3">Add new student</h4>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('students.store') }}">
            @csrf
            @include('students._form', ['student' => null])
            <div class="mt-3">
                <button class="btn btn-primary">Save student</button>
                <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
