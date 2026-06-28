@extends('layouts.app')
@section('title', 'Bulk import teachers')

@section('content')
<h4 class="mb-3">Bulk import teachers</h4>

@if (session('import_result') && session('import_result.type') === 'teachers')
    @php $r = session('import_result'); @endphp
    <div class="alert alert-{{ $r['errors'] ? 'warning' : 'success' }}">
        <strong>Imported:</strong> {{ $r['created'] }} created, {{ $r['skipped'] }} skipped.
        @if (! empty($r['errors']))
            <hr class="my-2">
            <ul class="mb-0 small">
                @foreach (array_slice($r['errors'], 0, 20) as $e)
                    <li>{{ $e }}</li>
                @endforeach
                @if (count($r['errors']) > 20)
                    <li class="text-muted">… and {{ count($r['errors']) - 20 }} more</li>
                @endif
            </ul>
        @endif
    </div>
@endif

<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('teachers.import.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Excel file (.xlsx, .xls) or CSV</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Max 10 MB.  The first row must contain the column headers.</div>
                    </div>
                    <button class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Upload and import
                    </button>
                    <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card border-info">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-info-circle text-info me-1"></i> Expected columns</h6>
                <p class="small text-muted mb-2">First row of the spreadsheet is the header. Order does not matter.</p>
                <ul class="small mb-3">
                    <li><code>first_name</code> <span class="text-danger">*</span></li>
                    <li><code>last_name</code> <span class="text-danger">*</span></li>
                    <li><code>email</code> <span class="text-danger">*</span> <em class="text-muted">(must be unique)</em></li>
                    <li><code>employee_id</code> <em class="text-muted">(optional, must be unique)</em></li>
                    <li><code>phone</code></li>
                    <li><code>qualification</code></li>
                    <li><code>hire_date</code> <em class="text-muted">(YYYY-MM-DD)</em></li>
                    <li><code>gender</code> <em class="text-muted">(male / female / other)</em></li>
                    <li><code>address</code></li>
                    <li><code>salary</code> <em class="text-muted">(number)</em></li>
                    <li><code>status</code> <em class="text-muted">(working / resigned / transfer)</em></li>
                    <li><code>subject_code</code> <em class="text-muted">(must match an existing subject code)</em></li>
                </ul>
                <a href="{{ route('teachers.sample') }}" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-download me-1"></i> Download sample template (.xlsx)
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
