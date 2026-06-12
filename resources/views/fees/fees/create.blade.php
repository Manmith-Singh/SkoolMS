@extends('layouts.app')
@section('title', 'Assign fee')

@section('content')
<h4 class="mb-3">Assign fee</h4>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('fees.store') }}">
        @csrf
        @include('fees.fees._form', ['students' => $students, 'categories' => $categories, 'classes' => $classes])
        <div class="mt-3">
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Assign</button>
            <a href="{{ route('fees.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
