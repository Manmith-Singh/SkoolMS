@extends('layouts.app')
@section('title', 'Academic Years — ' . $tenant->name)

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Academic Years — {{ $tenant->name }}</h4>
    <a href="{{ route('master.tenants.academic-years.create', $tenant) }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> New Academic Year
    </a>
</div>

@if(session('stats'))
<div class="alert alert-success">
    <strong>{{ session('success') }}</strong>
    @php $stats = session('stats'); @endphp
    @if(isset($stats['duplicate']))
    <ul class="mb-0 mt-1">
        @foreach($stats['duplicate'] as $key => $val)
            <li>{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $val }}</li>
        @endforeach
    </ul>
    @endif
    @if(isset($stats['promote']))
    <ul class="mb-0 mt-1">
        @foreach($stats['promote'] as $key => $val)
            <li>{{ ucfirst($key) }}: {{ $val }}</li>
        @endforeach
    </ul>
    @endif
</div>
@elseif(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr>
                    <th>Year</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th class="no-sort">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($years as $y)
                <tr>
                    <td><strong>{{ $y->year_label }}</strong></td>
                    <td>{{ $y->start_date->format('d M Y') }}</td>
                    <td>{{ $y->end_date->format('d M Y') }}</td>
                    <td>
                        @if($y->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @unless($y->is_active)
                        <form method="POST" action="{{ route('master.tenants.academic-years.set-active', $tenant) }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="year_id" value="{{ $y->id }}">
                            <button class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i> Set Active</button>
                        </form>
                        @endunless
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($years->count() > 1)
<div class="card mt-3">
    <div class="card-header bg-white"><strong>Duplicate Data</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('master.tenants.academic-years.duplicate', $tenant) }}" class="row g-2">
            @csrf
            <div class="col-md-4">
                <label class="form-label small">From Year</label>
                <select name="from_year_id" class="form-select" required>
                    @foreach($years as $y)
                        <option value="{{ $y->id }}" @selected($y->is_active)>{{ $y->year_label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">To Year</label>
                <select name="to_year_id" class="form-select" required>
                    @foreach($years as $y)
                        <option value="{{ $y->id }}" @selected(!$y->is_active && $loop->first)>{{ $y->year_label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-copy me-1"></i> Duplicate</button>
            </div>
        </form>
        <small class="text-muted">Copies classes, subjects, fee categories, and exam types from the source year.</small>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-white"><strong>Promote Students</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('master.tenants.academic-years.promote', $tenant) }}" class="row g-2">
            @csrf
            <div class="col-md-4">
                <label class="form-label small">From Year</label>
                <select name="from_year_id" class="form-select" required>
                    @foreach($years as $y)
                        <option value="{{ $y->id }}" @selected($y->is_active)>{{ $y->year_label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">To Year</label>
                <select name="to_year_id" class="form-select" required>
                    @foreach($years as $y)
                        <option value="{{ $y->id }}" @selected(!$y->is_active && $loop->first)>{{ $y->year_label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-warning w-100"><i class="fas fa-arrow-up me-1"></i> Promote</button>
            </div>
        </form>
        <small class="text-muted">Moves each student to the next class. Final-year students are marked as Graduated.</small>
    </div>
</div>
@endif
@endsection
