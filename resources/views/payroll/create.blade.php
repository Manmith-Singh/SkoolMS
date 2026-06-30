@extends('layouts.app')
@section('title', 'Generate Payroll')

@section('content')
<h4 class="mb-3">Generate Payroll</h4>

<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('payroll.bulk-generate') }}">
        @csrf
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Month</label>
                <input type="month" name="month" class="form-control @error('month') is-invalid @enderror"
                    value="{{ old('month', date('Y-m')) }}" required>
                @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary"><i class="fas fa-cogs me-1"></i>Generate for all active teachers</button>
            </div>
        </div>
    </form>
</div></div>

<div class="mt-3">
    <a href="{{ route('payroll.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Payroll</a>
</div>
@endsection
