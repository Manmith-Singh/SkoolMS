@extends('layouts.app')
@section('title', 'Edit class')

@section('content')
<h4 class="mb-3">Edit class</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('classes.update', $class) }}">
        @csrf @method('PUT')
        @include('classes._form')
        <div class="mt-3">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('classes.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
