@extends('layouts.app')
@section('title', 'Record income')

@section('content')
<h4 class="mb-3">Record income</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('income.store') }}">
        @include('income._form')
        <div class="mt-3">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('income.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
