<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Exam;
use App\Models\Tenant\Result;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\Subject;
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
        $query = Result::with(['exam.subject', 'exam.classes', 'student']);

        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->integer('exam_id'));
        }
        if ($request->filled('class_id')) {
            $query->whereHas('student', fn ($q) => $q->where('class_id', $request->integer('class_id')));
        }

        $results = $query->orderByDesc('id')->limit(500)->get();
        $exams   = Exam::orderByDesc('date')->limit(100)->get();
        $classes = SchoolClass::orderBy('name')->get();

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
        $classId = $request->integer('class_id');

        $students = Student::with(['attendance' => function ($q) use ($month) {
            $q->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$month]);
        }])->when($classId, fn ($q) => $q->where('class_id', $classId))->get();

        $classes = SchoolClass::orderBy('name')->get();

        return view('reports.attendance', compact('students', 'classes', 'month', 'classId'));
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
}
