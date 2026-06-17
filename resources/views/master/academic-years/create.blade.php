@extends('layouts.app')
@section('title', 'New Academic Year — ' . $tenant->name)

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">New Academic Year — {{ $tenant->name }}</h4>
    <a href="{{ route('master.tenants.academic-years', $tenant) }}" class="btn btn-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('master.tenants.academic-years.store', $tenant) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Year Label *</label>
                    <input type="text" name="year_label" value="{{ old('year_label', date('Y') . '-' . (date('Y')+1)) }}" class="form-control" required placeholder="e.g. 2025-2026">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date *</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control" required>
                </div>
            </div>

            @if($previousYear)
            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" name="duplicate" value="1" id="chkDuplicate" class="form-check-input" checked>
                        <label class="form-check-label" for="chkDuplicate">
                            Duplicate data from <strong>{{ $previousYear->year_label }}</strong>
                            <small class="d-block text-muted">Classes, subjects, fee categories, and exam types will be copied.</small>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" name="promote" value="1" id="chkPromote" class="form-check-input">
                        <label class="form-check-label" for="chkPromote">
                            Promote students from <strong>{{ $previousYear->year_label }}</strong>
                            <small class="d-block text-muted">Each student moves to the next class. Final-year students are marked Graduated.</small>
                        </label>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info mt-3 mb-0">This is the first academic year. No data to duplicate.</div>
            @endif

            <div class="mt-4">
                <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Create Academic Year</button>
            </div>
        </form>
    </div>
</div>
@endsection
