@extends('layouts.app')
@section('title', 'Record payment')

@section('content')
<h4 class="mb-3">Record payment</h4>

<div class="card mb-3"><div class="card-body">
    <h6 class="card-title">Step 1: Find student</h6>
    <div class="row g-2">
        <div class="col-md-6">
            <input type="text" id="student-search" class="form-control" placeholder="Search by name, admission no, or phone..." autocomplete="off" value="{{ $selectedStudent ? $selectedStudent->full_name.' ('.$selectedStudent->admission_no.')' : '' }}">
        </div>
        <div class="col-md-2">
            <button id="search-btn" class="btn btn-outline-primary w-100"><i class="fas fa-search"></i> Search</button>
        </div>
    </div>
    <div id="search-results" class="mt-2" style="display:none;"></div>
</div></div>

<div id="step-2" style="display:{{ $selectedStudent ? 'block' : 'none' }};">
    <div class="card mb-3"><div class="card-body">
        <h6 class="card-title">
            Step 2: Select fee to pay
            @if($selectedStudent)
                <span class="badge bg-info ms-2">{{ $selectedStudent->full_name }} ({{ $selectedStudent->admission_no }}) — {{ $selectedStudent->schoolClass?->display_name }}</span>
            @else
                <span id="selected-student-info" class="badge bg-info ms-2" style="display:none;"></span>
            @endif
        </h6>
        <div id="fee-list">
            @if($selectedStudent && $studentFees->isNotEmpty())
                <div class="list-group">
                @foreach($studentFees as $f)
                    <button type="button" class="list-group-item list-group-item-action fee-item" data-id="{{ $f->id }}" data-balance="{{ $f->balance() }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><strong>{{ $f->category->name ?? '—' }}</strong> &mdash; Due: {{ $f->due_date?->format('d M Y') }}</div>
                            <div class="text-end">
                                <span class="badge bg-{{ $f->status === 'overdue' ? 'danger' : ($f->status === 'partial' ? 'warning' : 'secondary') }} me-2">{{ $f->status }}</span>
                                <span class="fw-bold">{{ number_format($f->balance(), 2) }} due</span>
                            </div>
                        </div>
                    </button>
                @endforeach
                </div>
            @elseif($selectedStudent)
                <div class="alert alert-info mb-0">No pending fees for this student.</div>
            @endif
        </div>
    </div></div>

    <div class="card" id="payment-form-card" style="display:none;"><div class="card-body">
        <h6 class="card-title">Step 3: Payment details</h6>
        <form method="POST" action="{{ route('fees.payments.store') }}">
            @csrf
            <input type="hidden" name="fee_id" id="selected-fee-id">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Amount *</label>
                    <input type="number" step="0.01" min="0.01" name="amount_paid" id="amount-paid" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment date *</label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', today()->toDateString()) }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mode *</label>
                    <select name="mode" class="form-select" required>
                        @foreach(['cash','cheque','bank_transfer','card','online','other'] as $m)
                            <option value="{{ $m }}" @selected(old('mode', 'cash') === $m)>{{ ucfirst(str_replace('_',' ', $m)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Transaction ref</label>
                    <input type="text" name="transaction_ref" value="{{ old('transaction_ref') }}" class="form-control" placeholder="Cheque no / txn id">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-success"><i class="fas fa-save me-1"></i> Record & generate receipt</button>
                <a href="{{ route('fees.payments.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
var studentFeesData = {!! $studentFeesJson !!};

function selectStudent(id, label) {
    $('#search-results').hide();
    $('#selected-student-info').text(label).show();
    $('#step-2').show();
    $('#payment-form-card').hide();
    $('#fee-list').html('<div class="text-muted small p-2">Loading...</div>');

    $.get('{{ route("fees.payments.create") }}', { student_id: id }, function(html) {
        var newDoc = $('<div>').html(html);
        var newFeeList = newDoc.find('#fee-list').html();
        if (newFeeList) {
            $('#fee-list').html(newFeeList);
            attachFeeClickHandlers();
        }
    });

    window.history.replaceState({}, '', '{{ route("fees.payments.create") }}?student_id=' + id);
}

function attachFeeClickHandlers() {
    $('.fee-item').on('click', function() {
        var id = $(this).data('id');
        var balance = $(this).data('balance');
        $('#selected-fee-id').val(id);
        $('#amount-paid').val(balance).attr('max', balance);
        $('#payment-form-card').show();
        $('.fee-item').removeClass('active');
        $(this).addClass('active');
    });
}

$(function() {
    attachFeeClickHandlers();

    function doSearch() {
        var q = $('#student-search').val().trim();
        if (q.length < 1) return;

        $('#search-results').show().html('<div class="text-muted small p-2">Searching...</div>');

        $.getJSON('{{ route("fees.payments.search-student") }}', { q: q }, function(data) {
            if (!data.length) {
                $('#search-results').html('<div class="text-muted small p-2">No students found.</div>');
                return;
            }
            var html = '<div class="list-group">';
            $.each(data, function(i, s) {
                html += '<button type="button" class="list-group-item list-group-item-action search-result-item" data-id="' + s.id + '" data-label="' + $('<span>').text(s.text).html() + '">';
                html += '<div class="d-flex justify-content-between"><span>' + s.text + '</span><small class="text-muted">' + (s.class_name || '') + '</small></div>';
                html += '</button>';
            });
            html += '</div>';
            $('#search-results').html(html);

            $('.search-result-item').on('click', function() {
                selectStudent($(this).data('id'), $(this).data('label'));
            });
        });
    }

    $('#search-btn').on('click', doSearch);
    $('#student-search').on('keyup', function(e) {
        if (e.key === 'Enter') doSearch();
    });
});
</script>
@endpush
