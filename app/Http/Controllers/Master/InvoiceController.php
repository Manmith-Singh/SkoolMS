<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\AuditLog;
use App\Models\Master\Invoice;
use App\Models\Master\SubscriptionPlan;
use App\Models\Master\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Invoice::with(['tenant', 'subscription.plan'])->orderByDesc('id');

        if ($q = trim((string) $request->get('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('invoice_number', 'like', "%{$q}%")
                  ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$q}%"));
            });
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(25)->withQueryString();

        $totals = [
            'total'   => (clone $query)->sum('total'),
            'paid'    => (clone $query)->where('status', 'paid')->sum('total'),
            'overdue' => (clone $query)->where('status', 'overdue')->sum('total'),
        ];

        return view('master.invoices.index', compact('invoices', 'totals'));
    }

    public function create(): View
    {
        $tenants = Tenant::orderBy('name')->get();
        $plans   = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('master.invoices.create', compact('tenants', 'plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tenant_id'      => ['required', 'integer', 'exists:tenants,id'],
            'plan_id'        => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'currency'       => ['required', 'string', 'size:3'],
            'issue_date'     => ['required', 'date'],
            'due_date'       => ['required', 'date', 'after_or_equal:issue_date'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:191'],
            'items.*.quantity'    => ['required', 'integer', 'min:1'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
        ]);

        $subtotal = 0;
        foreach ($data['items'] as $row) {
            $subtotal += $row['quantity'] * $row['unit_price'];
        }

        $invoice = Invoice::create([
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad((string) (Invoice::max('id') + 1), 5, '0', STR_PAD_LEFT),
            'tenant_id'      => $data['tenant_id'],
            'subscription_id'=> null,
            'subtotal'       => $subtotal,
            'tax'            => 0,
            'total'          => $subtotal,
            'currency'       => $data['currency'],
            'status'         => 'sent',
            'issue_date'     => $data['issue_date'],
            'due_date'       => $data['due_date'],
            'notes'          => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $row) {
            $invoice->items()->create([
                'description' => $row['description'],
                'quantity'    => $row['quantity'],
                'unit_price'  => $row['unit_price'],
                'amount'      => $row['quantity'] * $row['unit_price'],
            ]);
        }

        AuditLog::record('invoice.created', $invoice, ['tenant_id' => $invoice->tenant_id, 'total' => $invoice->total]);

        return redirect()->route('master.invoices.index')->with('success', "Invoice {$invoice->invoice_number} created.");
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load('tenant', 'items', 'payments');

        return view('master.invoices.show', compact('invoice'));
    }

    public function markPaid(Invoice $invoice): RedirectResponse
    {
        $invoice->update(['status' => 'paid', 'paid_at' => now()]);
        AuditLog::record('invoice.marked_paid', $invoice);

        // Auto-reactivate tenant if they were suspended due to expiry
        $tenant = $invoice->tenant;
        if ($tenant && $tenant->status === 'suspended' && $tenant->plan) {
            $reactivated = $tenant->reactivateAfterPayment();
            if ($reactivated) {
                AuditLog::record('tenant.auto_reactivated', $tenant, [
                    'name'      => $tenant->name,
                    'subdomain' => $tenant->subdomain,
                    'reason'    => 'invoice_marked_paid',
                    'invoice'   => $invoice->invoice_number,
                ]);
            }
        }

        return back()->with('success', "Invoice {$invoice->invoice_number} marked as paid.");
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $num = $invoice->invoice_number;
        $invoice->delete();
        AuditLog::record('invoice.deleted', null, ['number' => $num]);

        return redirect()->route('master.invoices.index')->with('success', "Invoice {$num} deleted.");
    }
}
