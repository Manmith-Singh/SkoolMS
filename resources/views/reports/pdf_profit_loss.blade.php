<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Profit & Loss Report</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
th { background: #f0f0f0; }
.text-end { text-align: right; }
.text-success { color: #198754; }
.text-danger { color: #dc3545; }
.text-primary { color: #0d6efd; }
</style></head>
<body>
<h2>Profit & Loss Report</h2>
<p>Period: {{ $start }} to {{ $end }} ({{ $period }})</p>

<h3>Summary</h3>
<table>
    <tr><td><strong>Total Income</strong></td><td class="text-end text-success">{{ number_format($totalIncome, 2) }}</td></tr>
    <tr><td><strong>Total Expenditure</strong></td><td class="text-end text-danger">{{ number_format($totalExpenditure, 2) }}</td></tr>
    <tr style="font-weight:700; font-size:14px;"><td><strong>Net {{ $net >= 0 ? 'Profit' : 'Loss' }}</strong></td><td class="text-end {{ $net >= 0 ? 'text-primary' : 'text-danger' }}">{{ number_format($net, 2) }}</td></tr>
</table>

<h3>Income by type</h3>
<table>
    <thead><tr><th>Category</th><th class="text-end">Amount</th></tr></thead>
    <tbody>@foreach($incomeByType as $type)<tr><td>{{ $type->name ?? 'Uncategorized' }}</td><td class="text-end">{{ number_format($type->total, 2) }}</td></tr>@endforeach</tbody>
    <tfoot><tr><th>Total</th><th class="text-end">{{ number_format(collect($incomeByType)->sum('total'), 2) }}</th></tr></tfoot>
</table>

<h3>Expenditure by type</h3>
<table>
    <thead><tr><th>Category</th><th class="text-end">Amount</th></tr></thead>
    <tbody>@foreach($expenditureByType as $type)<tr><td>{{ $type->name ?? 'Uncategorized' }}</td><td class="text-end">{{ number_format($type->total, 2) }}</td></tr>@endforeach</tbody>
    <tfoot><tr><th>Total</th><th class="text-end">{{ number_format(collect($expenditureByType)->sum('total'), 2) }}</th></tr></tfoot>
</table>
</body></html>
