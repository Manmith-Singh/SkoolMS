@extends('layouts.app')
@section('title', 'Add subject')

@section('content')
<h4 class="mb-3">Add subject</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('subjects.store') }}">
        @csrf
        @include('subjects._form', ['subject' => null])
        <div class="mt-3">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('subjects.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
