@extends('layouts.app')
@section('title', 'Edit expenditure')

@section('content')
<h4 class="mb-3">Edit expenditure</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('expenditure.update', $expenditure) }}">
        @csrf @method('PUT')
        @include('expenditure._form')
        <div class="mt-3">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('expenditure.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
