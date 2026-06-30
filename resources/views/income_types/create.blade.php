@extends('layouts.app')
@section('title', 'Create income type')

@section('content')
<h4 class="mb-3">Create income type</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('income-types.store') }}">
        @include('income_types._form')
        <div class="mt-3">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('income-types.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
