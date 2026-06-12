@extends('layouts.app')
@section('title', 'Add teacher')

@section('content')
<h4 class="mb-3">Add teacher</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('teachers.store') }}">
        @csrf
        @include('teachers._form', ['teacher' => null])
        <div class="mt-3">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
