<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $payment->receipt_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6fb; }
        .receipt { max-width: 720px; margin: 2rem auto; background:#fff; padding: 2.5rem; border-radius: 8px; box-shadow:0 2px 6px rgba(0,0,0,.05); }
        .receipt-header { border-bottom: 3px solid #3b6db5; padding-bottom: 1rem; }
        .receipt h1 { color:#3b6db5; }
        @media print {
            body { background:#fff; }
            .no-print { display: none !important; }
            .receipt { box-shadow: none; margin:0; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="d-flex justify-content-between align-items-start receipt-header">
            <div>
                <h1 class="h3 mb-0">{{ $currentTenant->name ?? 'School' }}</h1>
                <small class="text-muted">{{ $currentTenant->address ?? '' }}</small>
            </div>
            <div class="text-end">
                <h4 class="mb-0">FEE RECEIPT</h4>
                <small class="text-muted">No. <strong>{{ $payment->receipt_no }}</strong></small>
            </div>
        </div>

        <table class="table table-borderless mt-4">
            <tr>
                <td><strong>Student:</strong> {{ $payment->student->full_name }}</td>
                <td><strong>Adm No:</strong> <code>{{ $payment->student->admission_no }}</code></td>
            </tr>
            <tr>
                <td><strong>Class:</strong> {{ $payment->student->schoolClass->display_name ?? '—' }}</td>
                <td><strong>Guardian:</strong> {{ $payment->student->guardian_name ?? '—' }} ({{ $payment->student->guardian_phone ?? '—' }})</td>
            </tr>
            <tr>
                <td><strong>Fee category:</strong> {{ $payment->fee->category->name ?? '—' }}</td>
                <td><strong>Payment date:</strong> {{ $payment->payment_date->format('d M Y') }}</td>
            </tr>
        </table>

        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $payment->fee->category->name ?? 'Fee' }} — total</td>
                    <td class="text-end">{{ number_format($payment->fee->amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Previously paid</td>
                    <td class="text-end">{{ number_format(((float) $payment->fee->paid_amount) - (float) $payment->amount_paid, 2) }}</td>
                </tr>
                <tr class="table-active">
                    <th>Paid today ({{ ucfirst(str_replace('_',' ', $payment->mode)) }})</th>
                    <th class="text-end text-success">{{ number_format($payment->amount_paid, 2) }}</th>
                </tr>
                <tr>
                    <th>Balance</th>
                    <th class="text-end text-danger">{{ number_format(((float) $payment->fee->amount) - ((float) $payment->fee->paid_amount), 2) }}</th>
                </tr>
            </tbody>
        </table>

        @if($payment->transaction_ref)
            <p class="small text-muted">Transaction ref: {{ $payment->transaction_ref }}</p>
        @endif

        <div class="d-flex justify-content-between mt-5">
            <div>Received by<br><br>______________________<br><small>{{ $payment->received_by ?? '—' }}</small></div>
            <div class="text-end">Authorised signatory<br><br>______________________<br><small>{{ $currentTenant->name ?? 'School' }}</small></div>
        </div>

        <p class="text-center text-muted small mt-4">This is a computer-generated receipt. No signature is required for digital copies.</p>
    </div>

    <div class="text-center no-print mb-4">
        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
        <a href="{{ route('fees.payments.index') }}" class="btn btn-secondary">Back</a>
    </div>
</body>
</html>
