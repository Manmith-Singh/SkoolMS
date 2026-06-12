<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\AuditLog;
use App\Models\Master\Invoice;
use App\Models\Master\Payment;
use App\Models\Master\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::with(['tenant', 'invoice'])->orderByDesc('id');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($method = $request->get('method')) {
            $query->where('method', $method);
        }

        $payments = $query->paginate(25)->withQueryString();

        $stats = [
            'succeeded' => (float) Payment::where('status', 'succeeded')->sum('amount'),
            'pending'   => (float) Payment::where('status', 'pending')->sum('amount'),
            'failed'    => (float) Payment::where('status', 'failed')->sum('amount'),
            'count'     => Payment::count(),
        ];

        return view('master.payments.index', compact('payments', 'stats'));
    }

    public function create(): View
    {
        $tenants  = Tenant::orderBy('name')->get();
        $invoices = Invoice::whereIn('status', ['sent', 'overdue'])->orderByDesc('id')->limit(200)->get();

        return view('master.payments.create', compact('tenants', 'invoices'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tenant_id'  => ['required', 'integer', 'exists:tenants,id'],
            'invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
            'amount'     => ['required', 'numeric', 'min:0'],
            'currency'   => ['required', 'string', 'size:3'],
            'method'     => ['required', 'in:cash,bank_transfer,cheque,card,online,other'],
            'reference'  => ['nullable', 'string', 'max:191'],
            'status'     => ['required', 'in:pending,succeeded,failed'],
            'paid_at'    => ['nullable', 'date'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        if (! empty($data['invoice_id'])) {
            $invoice = Invoice::find($data['invoice_id']);
            if ($invoice && $invoice->tenant_id != $data['tenant_id']) {
                return back()->withInput()->withErrors(['invoice_id' => 'Invoice does not belong to the chosen tenant.']);
            }
        }

        $payment = Payment::create($data + [
            'paid_at' => $data['status'] === 'succeeded' ? ($data['paid_at'] ?? now()) : null,
        ]);

        if ($payment->invoice && $payment->status === 'succeeded') {
            $payment->invoice->refresh();
            if ($payment->invoice->amountDue() <= 0) {
                $payment->invoice->update(['status' => 'paid', 'paid_at' => now()]);
            }
        }

        AuditLog::record('payment.recorded', $payment, ['amount' => $payment->amount, 'method' => $payment->method]);

        return redirect()->route('master.payments.index')->with('success', "Payment of {$payment->amount} {$payment->currency} recorded.");
    }
}
