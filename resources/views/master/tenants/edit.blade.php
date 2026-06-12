@extends('layouts.app')
@section('title', 'Edit Tenant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Edit — {{ $tenant->name }}</h3>
    <a href="{{ route('master.tenants.index') }}" class="btn btn-link">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('master.tenants.update', $tenant) }}">
            @csrf @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">School name</label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subdomain (read-only)</label>
                    <input type="text" value="{{ $tenant->subdomain }}" class="form-control" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $tenant->contact_email) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $tenant->contact_phone) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Plan</label>
                    <select name="plan_id" class="form-select">
                        <option value="">— No plan —</option>
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}" @selected(old('plan_id', $tenant->plan_id) == $p->id)>{{ $p->name }} ({{ number_format($p->price, 2) }} {{ $p->currency }}/{{ $p->billing_period }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subscription ends at</label>
                    <input type="date" name="subscription_ends_at" value="{{ old('subscription_ends_at', $tenant->subscription_ends_at?->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $tenant->address) }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
