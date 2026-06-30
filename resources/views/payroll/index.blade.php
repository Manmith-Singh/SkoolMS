@extends('layouts.app')
@section('title', 'Payroll')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Payroll</h4>
    <div>
        <a href="{{ route('payroll.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Generate Payroll</a>
        <form method="POST" action="{{ route('payroll.bulk-pay') }}" class="d-inline" id="bulk-pay-form">
            @csrf
            <button class="btn btn-success" type="submit" onclick="return confirm('Mark all pending payrolls as paid?')"><i class="fas fa-check-double me-1"></i>Bulk Pay</button>
        </form>
    </div>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Month</label>
            <input type="month" name="month" value="{{ request('month') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="pending" @selected(request('status') == 'pending')>Pending</option>
                <option value="paid" @selected(request('status') == 'paid')>Paid</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Teacher</label>
            <select name="teacher_id" class="form-select">
                <option value="">All teachers</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}" @selected(request('teacher_id') == $t->id)>{{ $t->full_name ?? $t->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i> Filter</button>
        </div>
    </form>
</div></div>

<div class="card">
    <div class="card-body p-0">
        <table class="table datatable mb-0">
            <thead>
                <tr>
                    <th>Teacher</th>
                    <th>Month</th>
                    <th class="text-end">Gross</th>
                    <th class="text-end">PF</th>
                    <th class="text-end">ESI</th>
                    <th class="text-end">PT</th>
                    <th class="text-end">Deductions</th>
                    <th class="text-end">Net</th>
                    <th>Status</th>
                    <th>Payment date</th>
                    <th class="no-sort">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payrolls as $p)
                <tr>
                    <td>{{ $p->teacher->full_name ?? $p->teacher->name ?? '—' }}</td>
                    <td>{{ $p->month ? \Carbon\Carbon::parse($p->month.'-01')->format('M Y') : '—' }}</td>
                    <td class="text-end">{{ number_format($p->gross_salary, 2) }}</td>
                    <td class="text-end">{{ number_format($p->pf_deduction, 2) }}</td>
                    <td class="text-end">{{ number_format($p->esi_deduction, 2) }}</td>
                    <td class="text-end">{{ number_format($p->professional_tax, 2) }}</td>
                    <td class="text-end">{{ number_format($p->other_deductions + $p->income_tax, 2) }}</td>
                    <td class="text-end"><strong>{{ number_format($p->net_salary, 2) }}</strong></td>
                    <td>
                        <span class="badge bg-{{ $p->status == 'paid' ? 'success' : 'warning' }}">
                            {{ ucfirst($p->status) }}
                        </span>
                    </td>
                    <td>{{ $p->payment_date?->format('d M Y') ?? '—' }}</td>
                    <td>
                        <a href="{{ route('payroll.edit', $p) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('payroll.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Delete this payroll record?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-active">
                    <th colspan="2">Totals</th>
                    <th class="text-end">{{ number_format($payrolls->sum('gross_salary'), 2) }}</th>
                    <th class="text-end">{{ number_format($payrolls->sum('pf_deduction'), 2) }}</th>
                    <th class="text-end">{{ number_format($payrolls->sum('esi_deduction'), 2) }}</th>
                    <th class="text-end">{{ number_format($payrolls->sum('professional_tax'), 2) }}</th>
                    <th class="text-end">{{ number_format($payrolls->sum('other_deductions') + $payrolls->sum('income_tax'), 2) }}</th>
                    <th class="text-end">{{ number_format($payrolls->sum('net_salary'), 2) }}</th>
                    <th colspan="3"></th>
                </tr>
            </tfoot>
        </table>
    </div>
    @if(method_exists($payrolls, 'links'))
    <div class="card-footer">
        {{ $payrolls->links() }}
    </div>
    @endif
</div>
@endsection
