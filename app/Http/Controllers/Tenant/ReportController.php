<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Exam;
use App\Models\Tenant\ExpenditureTransaction;
use App\Models\Tenant\IncomeTransaction;
use App\Models\Tenant\Result;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\Subject;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    public function results(Request $request): View
    {
        $query = Result::with(['exam.subjects', 'exam.classes', 'student']);

        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->integer('exam_id'));
        }
        if ($request->filled('class_id')) {
            $query->whereHas('student', fn ($q) => $q->whereIn('class_id', (array) $request->input('class_id')));
        }

        $results = $query->orderByDesc('id')->limit(500)->get();
        $exams   = Exam::orderByDesc('from_date')->limit(100)->get();
        $classes = SchoolClass::orderBy('id')->get();

        $summary = [
            'total'      => $results->count(),
            'pass'       => $results->filter(fn ($r) => $r->isPass())->count(),
            'fail'       => $results->reject(fn ($r) => $r->isPass())->count(),
            'avg_pct'    => $results->avg(fn ($r) => $r->percentage()) ?: 0,
        ];

        return view('reports.results', compact('results', 'exams', 'classes', 'summary'));
    }

    public function attendance(Request $request): View
    {
        $month = $request->input('month', now()->format('Y-m'));
        $classIds = $request->input('class_id');

        $students = Student::with(['attendance' => function ($q) use ($month) {
            $q->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$month]);
        }]);

        if (! empty($classIds) && is_array($classIds)) {
            $classIds = array_values(array_filter($classIds));
            if (! empty($classIds)) {
                $students->whereIn('class_id', $classIds);
            }
        }

        $students = $students->get();
        $classes = SchoolClass::orderBy('id')->get();

        return view('reports.attendance', compact('students', 'classes', 'month', 'classIds'));
    }

    public function fees(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->endOfMonth()->toDateString());

        $collected = \App\Models\Tenant\FeePayment::whereBetween('payment_date', [$from, $to])->sum('amount_paid');
        $pending   = \App\Models\Tenant\Fee::whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum(\DB::raw('amount - paid_amount'));

        $byCategory = \App\Models\Tenant\FeePayment::with('fee.category')
            ->whereBetween('payment_date', [$from, $to])
            ->get()
            ->groupBy(fn ($p) => $p->fee->category->name ?? 'Uncategorised')
            ->map(fn ($g) => $g->sum('amount_paid'));

        return view('reports.fees', compact('collected', 'pending', 'byCategory', 'from', 'to'));
    }

    public function income(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $date = $request->input('date', now()->toDateString());

        $query = IncomeTransaction::with('incomeType');

        $start = match ($period) {
            'daily'    => $date,
            'weekly'   => now()->parse($date)->startOfWeek()->toDateString(),
            'monthly'  => now()->parse($date)->startOfMonth()->toDateString(),
            'yearly'   => now()->parse($date)->startOfYear()->toDateString(),
            default    => now()->startOfMonth()->toDateString(),
        };
        $end = match ($period) {
            'daily'    => $date,
            'weekly'   => now()->parse($date)->endOfWeek()->toDateString(),
            'monthly'  => now()->parse($date)->endOfMonth()->toDateString(),
            'yearly'   => now()->parse($date)->endOfYear()->toDateString(),
            default    => now()->endOfMonth()->toDateString(),
        };

        $query->whereBetween('date', [$start, $end]);
        $transactions = $query->orderBy('date')->get();
        $total = $transactions->sum('amount');

        $byType = $transactions->groupBy(fn ($t) => $t->incomeType->name ?? 'Uncategorised')
            ->map(fn ($g) => $g->sum('amount'));

        $export = $request->input('export');
        if ($export === 'xlsx') {
            $headers = ['Date', 'Type', 'Description', 'Amount', 'Reference', 'Received By'];
            $rows = $transactions->map(fn ($t) => [
                $t->date?->format('d M Y'),
                $t->incomeType->name ?? '—',
                $t->description ?? '—',
                (float) $t->amount,
                $t->reference ?? '—',
                $t->received_by ?? '—',
            ])->toArray();
            return app(ReportExportService::class)->streamXlsx("income-report-{$period}-{$start}.xlsx", $headers, $rows);
        }
        if ($export === 'pdf') {
            return app(ReportExportService::class)->downloadPdf('reports.pdf_income', compact('transactions', 'byType', 'total', 'period', 'start', 'end'), "income-report-{$period}-{$start}.pdf");
        }

        return view('reports.income', compact('transactions', 'byType', 'total', 'period', 'date', 'start', 'end'));
    }

    public function expenditure(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $date = $request->input('date', now()->toDateString());

        $query = ExpenditureTransaction::with('expenditureType');

        $start = match ($period) {
            'daily'    => $date,
            'weekly'   => now()->parse($date)->startOfWeek()->toDateString(),
            'monthly'  => now()->parse($date)->startOfMonth()->toDateString(),
            'yearly'   => now()->parse($date)->startOfYear()->toDateString(),
            default    => now()->startOfMonth()->toDateString(),
        };
        $end = match ($period) {
            'daily'    => $date,
            'weekly'   => now()->parse($date)->endOfWeek()->toDateString(),
            'monthly'  => now()->parse($date)->endOfMonth()->toDateString(),
            'yearly'   => now()->parse($date)->endOfYear()->toDateString(),
            default    => now()->endOfMonth()->toDateString(),
        };

        $query->whereBetween('date', [$start, $end]);
        $transactions = $query->orderBy('date')->get();
        $total = $transactions->sum('amount');

        $byType = $transactions->groupBy(fn ($t) => $t->expenditureType->name ?? 'Uncategorised')
            ->map(fn ($g) => $g->sum('amount'));

        $export = $request->input('export');
        if ($export === 'xlsx') {
            $headers = ['Date', 'Type', 'Description', 'Amount', 'Reference', 'Paid By', 'Approved By'];
            $rows = $transactions->map(fn ($t) => [
                $t->date?->format('d M Y'),
                $t->expenditureType->name ?? '—',
                $t->description ?? '—',
                (float) $t->amount,
                $t->reference ?? '—',
                $t->paid_by ?? '—',
                $t->approved_by ?? '—',
            ])->toArray();
            return app(ReportExportService::class)->streamXlsx("expenditure-report-{$period}-{$start}.xlsx", $headers, $rows);
        }
        if ($export === 'pdf') {
            return app(ReportExportService::class)->downloadPdf('reports.pdf_expenditure', compact('transactions', 'byType', 'total', 'period', 'start', 'end'), "expenditure-report-{$period}-{$start}.pdf");
        }

        return view('reports.expenditure', compact('transactions', 'byType', 'total', 'period', 'date', 'start', 'end'));
    }

    public function profitLoss(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $date = $request->input('date', now()->toDateString());

        $start = match ($period) {
            'daily'    => $date,
            'weekly'   => now()->parse($date)->startOfWeek()->toDateString(),
            'monthly'  => now()->parse($date)->startOfMonth()->toDateString(),
            'yearly'   => now()->parse($date)->startOfYear()->toDateString(),
            default    => now()->startOfMonth()->toDateString(),
        };
        $end = match ($period) {
            'daily'    => $date,
            'weekly'   => now()->parse($date)->endOfWeek()->toDateString(),
            'monthly'  => now()->parse($date)->endOfMonth()->toDateString(),
            'yearly'   => now()->parse($date)->endOfYear()->toDateString(),
            default    => now()->endOfMonth()->toDateString(),
        };

        $incomeTransactions = IncomeTransaction::with('incomeType')
            ->whereBetween('date', [$start, $end])->orderBy('date')->get();
        $expenditureTransactions = ExpenditureTransaction::with('expenditureType')
            ->whereBetween('date', [$start, $end])->orderBy('date')->get();

        $totalIncome = $incomeTransactions->sum('amount');
        $totalExpenditure = $expenditureTransactions->sum('amount');
        $net = $totalIncome - $totalExpenditure;

        $incomeByType = $incomeTransactions->groupBy(fn ($t) => $t->incomeType->name ?? 'Uncategorised')
            ->map(fn ($g) => $g->sum('amount'));
        $expenditureByType = $expenditureTransactions->groupBy(fn ($t) => $t->expenditureType->name ?? 'Uncategorised')
            ->map(fn ($g) => $g->sum('amount'));

        $export = $request->input('export');
        if ($export === 'xlsx') {
            $headers = ['Type', 'Category', 'Amount'];
            $rows = [];
            foreach ($incomeByType as $name => $amt) {
                $rows[] = ['Income', $name, (float) $amt];
            }
            foreach ($expenditureByType as $name => $amt) {
                $rows[] = ['Expenditure', $name, (float) $amt];
            }
            $rows[] = ['', 'NET', (float) $net];
            return app(ReportExportService::class)->streamXlsx("profit-loss-{$period}-{$start}.xlsx", $headers, $rows);
        }
        if ($export === 'pdf') {
            return app(ReportExportService::class)->downloadPdf('reports.pdf_profit_loss', compact('incomeByType', 'expenditureByType', 'totalIncome', 'totalExpenditure', 'net', 'period', 'start', 'end'), "profit-loss-{$period}-{$start}.pdf");
        }

        return view('reports.profit_loss', compact('incomeByType', 'expenditureByType', 'totalIncome', 'totalExpenditure', 'net', 'period', 'date', 'start', 'end'));
    }
}
