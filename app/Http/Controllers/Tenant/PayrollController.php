<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Payroll;
use App\Models\Tenant\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payroll::with('teacher')->orderByDesc('month')->orderBy('teacher_id');

        if ($request->filled('month')) {
            $query->where('month', $request->string('month'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->integer('teacher_id'));
        }

        $payrolls = $query->paginate(25)->withQueryString();
        $teachers = Teacher::orderBy('first_name')->get();

        $totals = (clone $query)->reorder()->select(
            DB::raw('COALESCE(SUM(gross_salary),0) as total_gross'),
            DB::raw('COALESCE(SUM(pf_deduction),0) as total_pf'),
            DB::raw('COALESCE(SUM(esi_deduction),0) as total_esi'),
            DB::raw('COALESCE(SUM(total_deductions),0) as total_deductions'),
            DB::raw('COALESCE(SUM(net_salary),0) as total_net')
        )->first();

        return view('payroll.index', compact('payrolls', 'teachers', 'totals'));
    }

    public function create(): View
    {
        return view('payroll.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:tenant.teachers,id'],
            'month'      => ['required', 'string', 'max:7'],
        ]);

        $teacher = Teacher::findOrFail($data['teacher_id']);
        $existing = Payroll::where('teacher_id', $data['teacher_id'])
            ->where('month', $data['month'])->first();

        if ($existing) {
            return back()->withErrors(['month' => 'Payroll already exists for this teacher/month.']);
        }

        $gross = ($teacher->basic_pay ?? 0) + ($teacher->hra ?? 0) + ($teacher->da ?? 0)
            + ($teacher->conveyance ?? 0) + ($teacher->medical_allowance ?? 0)
            + ($teacher->other_allowances ?? 0);
        $pf = round(($teacher->basic_pay ?? 0) * 0.12, 2);
        $esi = $gross <= 21000 ? round($gross * 0.0075, 2) : 0;
        $pt = 200;
        $totalDed = $pf + $esi + $pt;
        $net = round($gross - $totalDed, 2);

        Payroll::create([
            'teacher_id'        => $data['teacher_id'],
            'month'             => $data['month'],
            'gross_salary'      => $gross,
            'basic_pay'         => $teacher->basic_pay ?? 0,
            'hra'               => $teacher->hra ?? 0,
            'da'                => $teacher->da ?? 0,
            'conveyance'        => $teacher->conveyance ?? 0,
            'medical_allowance' => $teacher->medical_allowance ?? 0,
            'other_allowances'  => $teacher->other_allowances ?? 0,
            'pf_deduction'      => $pf,
            'esi_deduction'     => $esi,
            'professional_tax'  => $pt,
            'income_tax'        => 0,
            'other_deductions'  => 0,
            'total_deductions'  => $totalDed,
            'net_salary'        => $net,
            'status'            => 'pending',
        ]);

        return redirect()->route('payroll.index')->with('success', 'Payroll record created.');
    }

    public function edit(Payroll $payroll): View
    {
        $payroll->load('teacher');
        return view('payroll.edit', compact('payroll'));
    }

    public function update(Request $request, Payroll $payroll): RedirectResponse
    {
        $data = $request->validate([
            'gross_salary'      => ['required', 'numeric', 'min:0'],
            'basic_pay'         => ['required', 'numeric', 'min:0'],
            'hra'               => ['required', 'numeric', 'min:0'],
            'da'                => ['required', 'numeric', 'min:0'],
            'conveyance'        => ['required', 'numeric', 'min:0'],
            'medical_allowance' => ['required', 'numeric', 'min:0'],
            'other_allowances'  => ['required', 'numeric', 'min:0'],
            'pf_deduction'      => ['required', 'numeric', 'min:0'],
            'esi_deduction'     => ['required', 'numeric', 'min:0'],
            'professional_tax'  => ['required', 'numeric', 'min:0'],
            'income_tax'        => ['required', 'numeric', 'min:0'],
            'other_deductions'  => ['required', 'numeric', 'min:0'],
            'total_deductions'  => ['required', 'numeric', 'min:0'],
            'net_salary'        => ['required', 'numeric', 'min:0'],
            'payment_date'      => ['nullable', 'date'],
            'status'            => ['required', 'in:pending,paid'],
            'notes'             => ['nullable', 'string'],
        ]);

        $payroll->update($data);

        return redirect()->route('payroll.index')->with('success', 'Payroll updated.');
    }

    public function destroy(Payroll $payroll): RedirectResponse
    {
        $payroll->delete();
        return redirect()->route('payroll.index')->with('success', 'Payroll deleted.');
    }

    public function bulkGenerate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'month' => ['required', 'string', 'max:7'],
        ]);

        $teachers = Teacher::where('status', 'working')->get();
        $created = 0;

        foreach ($teachers as $teacher) {
            $exists = Payroll::where('teacher_id', $teacher->id)
                ->where('month', $data['month'])->exists();
            if ($exists) continue;

            $gross = ($teacher->basic_pay ?? 0) + ($teacher->hra ?? 0) + ($teacher->da ?? 0)
                + ($teacher->conveyance ?? 0) + ($teacher->medical_allowance ?? 0)
                + ($teacher->other_allowances ?? 0);
            $pf = round(($teacher->basic_pay ?? 0) * 0.12, 2);
            $esi = $gross <= 21000 ? round($gross * 0.0075, 2) : 0;
            $pt = 200;
            $totalDed = $pf + $esi + $pt;
            $net = round($gross - $totalDed, 2);

            Payroll::create([
                'teacher_id'        => $teacher->id,
                'month'             => $data['month'],
                'gross_salary'      => $gross,
                'basic_pay'         => $teacher->basic_pay ?? 0,
                'hra'               => $teacher->hra ?? 0,
                'da'                => $teacher->da ?? 0,
                'conveyance'        => $teacher->conveyance ?? 0,
                'medical_allowance' => $teacher->medical_allowance ?? 0,
                'other_allowances'  => $teacher->other_allowances ?? 0,
                'pf_deduction'      => $pf,
                'esi_deduction'     => $esi,
                'professional_tax'  => $pt,
                'income_tax'        => 0,
                'other_deductions'  => 0,
                'total_deductions'  => $totalDed,
                'net_salary'        => $net,
                'status'            => 'pending',
            ]);
            $created++;
        }

        return redirect()->route('payroll.index', ['month' => $data['month']])
            ->with('success', "Payroll generated for {$created} teacher(s).");
    }

    public function bulkPay(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'     => ['required', 'array'],
            'ids.*'   => ['exists:tenant.payrolls,id'],
            'payment_date' => ['required', 'date'],
        ]);

        Payroll::whereIn('id', $data['ids'])->update([
            'status'       => 'paid',
            'payment_date' => $data['payment_date'],
        ]);

        return back()->with('success', count($data['ids']) . ' payroll(s) marked as paid.');
    }
}
