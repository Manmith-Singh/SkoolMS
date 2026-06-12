@extends('layouts.app')
@section('title', 'New ticket')

@section('content')
<h3 class="mb-4">New support ticket</h3>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('master.tickets.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Subject <span class="text-danger">*</span></label>
            <input type="text" name="subject" value="{{ old('subject') }}" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Priority</label>
            <select name="priority" class="form-select">
                @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                    <option value="{{ $p }}" @selected(old('priority', 'medium') === $p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Category</label>
            <input type="text" name="category" value="{{ old('category') }}" class="form-control" placeholder="billing, bug, ...">
        </div>
        <div class="col-12">
            <label class="form-label">School (optional)</label>
            <select name="tenant_id" class="form-select">
                <option value="">— general ticket —</option>
                @foreach($tenants as $t)
                    <option value="{{ $t->id }}" @selected(old('tenant_id') == $t->id)>{{ $t->name }} ({{ $t->subdomain }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Message <span class="text-danger">*</span></label>
            <textarea name="message" class="form-control" rows="6" required>{{ old('message') }}</textarea>
        </div>
    </div>
    <div class="mt-4">
        <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Create ticket</button>
        <a href="{{ route('master.tickets.index') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>
@endsection
