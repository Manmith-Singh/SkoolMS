@extends('layouts.app')
@section('title', 'Fee assignments')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Fee assignments</h4>
    <a href="{{ route('fees.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Assign new fee</a>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="row g-2">
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(['pending','partial','paid','overdue','waived'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="class_id" class="form-select">
                <option value="">All classes</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" @selected(request('class_id') == $c->id)>{{ $c->display_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="category_id" class="form-select">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i> Filter</button></div>
    </form>
</div></div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr><th>Student</th><th>Class</th><th>Category</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Due</th><th>Status</th><th class="no-sort">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($fees as $f)
                <tr>
                    <td>{{ $f->student->full_name ?? '—' }}<br><small class="text-muted">{{ $f->student->admission_no ?? '' }}</small></td>
                    <td>{{ $f->student->schoolClass->display_name ?? '—' }}</td>
                    <td>{{ $f->category->name ?? '—' }}</td>
                    <td>{{ number_format($f->amount, 2) }}</td>
                    <td class="text-success">{{ number_format($f->paid_amount, 2) }}</td>
                    <td class="text-danger">{{ number_format($f->balance(), 2) }}</td>
                    <td>{{ $f->due_date->format('d M Y') }}</td>
                    <td><span class="badge badge-status-{{ $f->status }}">{{ ucfirst($f->status) }}</span></td>
                    <td>
                        @if(!$f->isFullyPaid())
                            <a href="{{ route('fees.payments.create', ['fee_id' => $f->id]) }}" class="btn btn-sm btn-success" title="Record payment">
                                <i class="fas fa-cash-register"></i>
                            </a>
                        @endif
                        <a href="{{ route('fees.show', $f) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                        <form method="POST" action="{{ route('fees.destroy', $f) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $fees->links() }}</div>
</div>
@endsection
