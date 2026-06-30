@extends('layouts.app')
@section('title', 'Edit Payroll')

@section('content')
<h4 class="mb-3">Edit Payroll</h4>

<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('payroll.update', $payroll) }}">
        @csrf @method('PUT')

        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <label class="form-label">Teacher</label>
                <select name="teacher_id" class="form-select" readonly disabled>
                    <option value="{{ $payroll->teacher_id }}">{{ $payroll->teacher->full_name ?? $payroll->teacher->name ?? '—' }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Month</label>
                <input type="text" class="form-control" value="{{ $payroll->month ? \Carbon\Carbon::parse($payroll->month.'-01')->format('F Y') : '—' }}" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="pending" @selected(old('status', $payroll->status) == 'pending')>Pending</option>
                    <option value="paid" @selected(old('status', $payroll->status) == 'paid')>Paid</option>
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <h6 class="text-muted mb-2">Earnings</h6>
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <label class="form-label">Gross salary</label>
                <input type="number" step="0.01" name="gross_salary" class="form-control @error('gross_salary') is-invalid @enderror amount-input"
                    value="{{ old('gross_salary', $payroll->gross_salary) }}" id="gross_salary">
                @error('gross_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Basic pay</label>
                <input type="number" step="0.01" name="basic_pay" class="form-control @error('basic_pay') is-invalid @enderror amount-input"
                    value="{{ old('basic_pay', $payroll->basic_pay) }}">
                @error('basic_pay')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">HRA</label>
                <input type="number" step="0.01" name="hra" class="form-control @error('hra') is-invalid @enderror amount-input"
                    value="{{ old('hra', $payroll->hra) }}">
                @error('hra')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">DA</label>
                <input type="number" step="0.01" name="da" class="form-control @error('da') is-invalid @enderror amount-input"
                    value="{{ old('da', $payroll->da) }}">
                @error('da')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Conveyance</label>
                <input type="number" step="0.01" name="conveyance" class="form-control @error('conveyance') is-invalid @enderror amount-input"
                    value="{{ old('conveyance', $payroll->conveyance) }}">
                @error('conveyance')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Medical allowance</label>
                <input type="number" step="0.01" name="medical_allowance" class="form-control @error('medical_allowance') is-invalid @enderror amount-input"
                    value="{{ old('medical_allowance', $payroll->medical_allowance) }}">
                @error('medical_allowance')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Other allowances</label>
                <input type="number" step="0.01" name="other_allowances" class="form-control @error('other_allowances') is-invalid @enderror amount-input"
                    value="{{ old('other_allowances', $payroll->other_allowances) }}">
                @error('other_allowances')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <h6 class="text-muted mb-2">Deductions</h6>
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <label class="form-label">PF deduction</label>
                <input type="number" step="0.01" name="pf_deduction" class="form-control @error('pf_deduction') is-invalid @enderror amount-input deduction"
                    value="{{ old('pf_deduction', $payroll->pf_deduction) }}" id="pf_deduction">
                @error('pf_deduction')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">ESI deduction</label>
                <input type="number" step="0.01" name="esi_deduction" class="form-control @error('esi_deduction') is-invalid @enderror amount-input deduction"
                    value="{{ old('esi_deduction', $payroll->esi_deduction) }}" id="esi_deduction">
                @error('esi_deduction')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Professional tax</label>
                <input type="number" step="0.01" name="professional_tax" class="form-control @error('professional_tax') is-invalid @enderror amount-input deduction"
                    value="{{ old('professional_tax', $payroll->professional_tax) }}" id="professional_tax">
                @error('professional_tax')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Income tax</label>
                <input type="number" step="0.01" name="income_tax" class="form-control @error('income_tax') is-invalid @enderror amount-input deduction"
                    value="{{ old('income_tax', $payroll->income_tax) }}" id="income_tax">
                @error('income_tax')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Other deductions</label>
                <input type="number" step="0.01" name="other_deductions" class="form-control @error('other_deductions') is-invalid @enderror amount-input deduction"
                    value="{{ old('other_deductions', $payroll->other_deductions) }}" id="other_deductions">
                @error('other_deductions')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <label class="form-label fw-bold">Net salary</label>
                <input type="number" step="0.01" name="net_salary" class="form-control @error('net_salary') is-invalid @enderror"
                    value="{{ old('net_salary', $payroll->net_salary) }}" id="net_salary" readonly>
                @error('net_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment date</label>
                <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror"
                    value="{{ old('payment_date', $payroll->payment_date?->format('Y-m-d')) }}">
                @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="1">{{ old('notes', $payroll->notes) }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mt-3">
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
            <a href="{{ route('payroll.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection

@push('scripts')
<script>
function calcNet() {
    const gross = parseFloat(document.getElementById('gross_salary').value) || 0;
    const pf = parseFloat(document.getElementById('pf_deduction').value) || 0;
    const esi = parseFloat(document.getElementById('esi_deduction').value) || 0;
    const pt = parseFloat(document.getElementById('professional_tax').value) || 0;
    const it = parseFloat(document.getElementById('income_tax').value) || 0;
    const other = parseFloat(document.getElementById('other_deductions').value) || 0;
    document.getElementById('net_salary').value = (gross - pf - esi - pt - it - other).toFixed(2);
}
document.querySelectorAll('.amount-input').forEach(el => el.addEventListener('input', calcNet));
document.addEventListener('DOMContentLoaded', calcNet);
</script>
@endpush
