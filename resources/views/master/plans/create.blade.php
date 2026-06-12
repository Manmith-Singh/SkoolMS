@extends('layouts.app')
@section('title', 'New plan')

@section('content')
<h3 class="mb-4">New subscription plan</h3>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('master.plans.store') }}">
    @csrf
    @include('master.plans._form', ['plan' => null])
    <div class="mt-4">
        <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Create plan</button>
        <a href="{{ route('master.plans.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>
@endsection
