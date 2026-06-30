@extends('layouts.app')
@section('title', 'Edit income')

@section('content')
<h4 class="mb-3">Edit income</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('income.update', $income) }}">
        @csrf @method('PUT')
        @include('income._form')
        <div class="mt-3">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('income.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
