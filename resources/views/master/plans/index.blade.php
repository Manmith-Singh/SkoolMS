@extends('layouts.app')
@section('title', 'Subscription Plans')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Subscription Plans</h3>
    <a href="{{ route('master.plans.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New plan</a>
</div>

<div class="row g-3">
@foreach($plans as $plan)
    <div class="col-md-4">
        <div class="card h-100 {{ $plan->is_active ? '' : 'opacity-50' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h5 class="mb-0">{{ $plan->name }}</h5>
                    <span class="badge bg-{{ $plan->is_active ? 'success' : 'secondary' }}">{{ $plan->is_active ? 'active' : 'inactive' }}</span>
                </div>
                <p class="text-muted small mb-2">{{ $plan->slug }}</p>
                <h3 class="text-primary">{{ number_format($plan->price, 2) }} <small class="text-muted fs-6">{{ $plan->currency }}/{{ $plan->billing_period }}</small></h3>
                <ul class="small text-muted ps-3">
                    <li>Students: {{ $plan->max_students ?: 'unlimited' }}</li>
                    <li>Teachers: {{ $plan->max_teachers ?: 'unlimited' }}</li>
                    <li>Storage: {{ $plan->max_storage_mb ?: 'unlimited' }} MB</li>
                </ul>
                @if($plan->features)
                    <ul class="small">
                        @foreach($plan->features as $f)
                            <li><i class="fas fa-check text-success me-1"></i>{{ $f }}</li>
                        @endforeach
                    </ul>
                @endif
                <div class="text-muted small mb-3">{{ $plan->tenants_count }} school(s) on this plan</div>
                <div class="d-flex gap-2">
                    <a href="{{ route('master.plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary flex-fill"><i class="fas fa-edit me-1"></i>Edit</a>
                    <form method="POST" action="{{ route('master.plans.destroy', $plan) }}" onsubmit="return confirm('Delete this plan?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
</div>
@endsection
