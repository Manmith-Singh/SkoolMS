@extends('layouts.app')
@section('title', 'Edit fee category')

@section('content')
<h4 class="mb-3">Edit category</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('fees.categories.update', $category) }}">
        @csrf @method('PUT')
        @include('fees.categories._form')
        <div class="mt-3">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('fees.categories.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
