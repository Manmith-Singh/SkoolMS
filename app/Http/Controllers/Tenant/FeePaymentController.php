<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Fee;
use App\Models\Tenant\FeePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FeePaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = FeePayment::with(['student.schoolClass', 'fee.category']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->integer('student_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->date('to'));
        }

        $payments = $query->orderByDesc('payment_date')->paginate(25)->withQueryString();
        $totalCollected = (clone $query)->sum('amount_paid');

        return view('fees.payments.index', compact('payments', 'totalCollected'));
    }

    public function create(Request $request): View
    {
        $preselectedFee = null;
        if ($request->filled('fee_id')) {
            $preselectedFee = Fee::with(['student', 'category'])->find($request->integer('fee_id'));
        }
        return view('fees.payments.create', [
            'fee' => $preselectedFee,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fee_id'         => ['required', 'exists:tenant.fees,id'],
            'amount_paid'    => ['required', 'numeric', 'min:0.01'],
            'payment_date'   => ['required', 'date'],
            'mode'           => ['required', 'in:cash,cheque,bank_transfer,card,online,other'],
            'transaction_ref'=> ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string'],
        ]);

        $fee = Fee::with('category')->findOrFail($data['fee_id']);
        $balance = round(((float) $fee->amount) - ((float) $fee->paid_amount), 2);

        if ($data['amount_paid'] > $balance) {
            return back()
                ->withInput()
                ->withErrors(['amount_paid' => "Amount exceeds outstanding balance of {$balance}."]);
        }

        DB::transaction(function () use ($data, $fee) {
            $payment = FeePayment::create([
                'fee_id'          => $fee->id,
                'student_id'      => $fee->student_id,
                'amount_paid'     => $data['amount_paid'],
                'payment_date'    => $data['payment_date'],
                'mode'            => $data['mode'],
                'transaction_ref' => $data['transaction_ref'] ?? null,
                'receipt_no'      => $this->generateReceiptNo(),
                'notes'           => $data['notes'] ?? null,
                'received_by'     => Auth::user()?->name ?? 'system',
            ]);

            $fee->paid_amount = round(((float) $fee->paid_amount) + (float) $data['amount_paid'], 2);
            $balanceAfter = round(((float) $fee->amount) - $fee->paid_amount, 2);
            $fee->status = $balanceAfter <= 0 ? 'paid' : 'partial';
            $fee->save();

            session()->flash('last_receipt_id', $payment->id);
        });

        return redirect()->route('fees.payments.receipt', session('last_receipt_id'))
            ->with('success', 'Payment recorded. Receipt generated below.');
    }

    public function receipt(FeePayment $payment): View
    {
        $payment->load(['student.schoolClass', 'fee.category']);
        return view('fees.payments.receipt', compact('payment'));
    }

    public function destroy(FeePayment $payment): RedirectResponse
    {
        DB::transaction(function () use ($payment) {
            $fee = $payment->fee;
            $fee->paid_amount = round(((float) $fee->paid_amount) - (float) $payment->amount_paid, 2);
            $fee->status = $fee->paid_amount > 0
                ? 'partial'
                : ($fee->due_date < now() ? 'overdue' : 'pending');
            $fee->save();

            $payment->delete();
        });

        return back()->with('success', 'Payment reversed.');
    }

    protected function generateReceiptNo(): string
    {
        $prefix = 'RCP-' . now()->format('Ymd');
        $latest = FeePayment::where('receipt_no', 'like', "{$prefix}-%")
            ->orderByDesc('id')
            ->value('receipt_no');

        $seq = 1;
        if ($latest) {
            $seq = ((int) substr($latest, -4)) + 1;
        }
        return $prefix . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
