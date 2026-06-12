<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\Exam;
use App\Models\Tenant\Fee;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\Subject;
use App\Models\Tenant\Teacher;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'students'        => Student::count(),
            'teachers'        => Teacher::count(),
            'classes'         => SchoolClass::count(),
            'subjects'        => Subject::count(),
            'exams'           => Exam::count(),
            'fees_collected'  => FeePayment::sum('amount_paid'),
            'fees_pending'    => Fee::whereIn('status', ['pending', 'partial', 'overdue'])->sum(\DB::raw('amount - paid_amount')),
            'today_present'   => Attendance::where('date', today())->where('status', 'present')->count(),
            'today_absent'    => Attendance::where('date', today())->where('status', 'absent')->count(),
        ];

        $recentPayments = FeePayment::with(['student', 'fee.category'])
            ->orderByDesc('payment_date')
            ->limit(5)
            ->get();

        $recentStudents = Student::with('schoolClass')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // Last 6 months fee collection for the chart
        $months = [];
        $collected = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end   = Carbon::now()->subMonths($i)->endOfMonth();
            $months[]    = $start->format('M Y');
            $collected[] = (float) FeePayment::whereBetween('payment_date', [$start, $end])->sum('amount_paid');
        }

        return view('dashboard', compact('stats', 'recentPayments', 'recentStudents', 'months', 'collected'));
    }
}
