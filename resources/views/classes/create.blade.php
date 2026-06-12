@extends('layouts.app')
@section('title', 'Add class')

@section('content')
<h4 class="mb-3">Add class</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('classes.store') }}">
        @csrf
        @include('classes._form')
        <div class="mt-3">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('classes.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
