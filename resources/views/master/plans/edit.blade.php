@extends('layouts.app')
@section('title', 'Edit plan')

@section('content')
<h3 class="mb-4">Edit plan — {{ $plan->name }}</h3>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('master.plans.update', $plan) }}">
    @csrf @method('PUT')
    @include('master.plans._form', ['plan' => $plan])
    <div class="mt-4">
        <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save changes</button>
        <a href="{{ route('master.plans.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>
@endsection
