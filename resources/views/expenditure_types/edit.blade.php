@extends('layouts.app')
@section('title', 'Edit expenditure type')

@section('content')
<h4 class="mb-3">Edit expenditure type</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('expenditure-types.update', $expenditureType) }}">
        @csrf @method('PUT')
        @include('expenditure_types._form')
        <div class="mt-3">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('expenditure-types.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
