@extends('layouts.app')
@section('title', 'Edit subject')

@section('content')
<h4 class="mb-3">Edit subject</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('subjects.update', $subject) }}">
        @csrf @method('PUT')
        @include('subjects._form', ['subject' => $subject])
        <div class="mt-3">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('subjects.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
