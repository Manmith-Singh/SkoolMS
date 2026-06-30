<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Expenditure Report</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
th { background: #f0f0f0; }
.text-end { text-align: right; }
</style></head>
<body>
<h2>Expenditure Report</h2>
<p>Period: {{ $start }} to {{ $end }} ({{ $period }})</p>
<table><thead><tr><th>Date</th><th>Type</th><th>Description</th><th class="text-end">Amount</th><th>Paid by</th><th>Approved by</th></tr></thead>
<tbody>@foreach($transactions as $t)<tr><td>{{ $t->date?->format('d M Y') ?? '—' }}</td><td>{{ $t->expenditureType->name ?? '—' }}</td><td>{{ $t->description ?? '—' }}</td><td class="text-end">{{ number_format($t->amount, 2) }}</td><td>{{ $t->paid_by ?? '—' }}</td><td>{{ $t->approved_by ?? '—' }}</td></tr>@endforeach</tbody>
<tfoot><tr><th colspan="3">Total</th><th class="text-end">{{ number_format($total, 2) }}</th><th colspan="2"></th></tr></tfoot></table>
</body></html>
