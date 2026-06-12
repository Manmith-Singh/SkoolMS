<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\AuditLog;
use App\Models\Master\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $query = Tenant::with('plan')->orderByDesc('id');

        if ($q = trim((string) $request->get('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('subdomain', 'like', "%{$q}%")
                  ->orWhere('contact_email', 'like', "%{$q}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $tenants = $query->paginate(25)->withQueryString();

        $stats = [
            'total'     => Tenant::count(),
            'active'    => Tenant::where('status', 'active')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
            'trialing'  => Tenant::where('status', 'trial')->count(),
        ];

        return view('master.tenants.index', compact('tenants', 'stats'));
    }

    public function create(): View
    {
        $plans = \App\Models\Master\SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('master.tenants.create', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:191'],
            'subdomain'      => ['required', 'string', 'max:63', 'regex:/^[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?$/', 'unique:tenants,subdomain'],
            'db_name'        => ['required', 'string', 'max:64', 'unique:tenants,db_name'],
            'db_user'        => ['nullable', 'string', 'max:64'],
            'db_password'    => ['nullable', 'string', 'max:255'],
            'admin_name'     => ['required', 'string', 'max:191'],
            'admin_email'    => ['required', 'email', 'max:191', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
            'contact_phone'  => ['nullable', 'string', 'max:30'],
            'address'        => ['nullable', 'string', 'max:500'],
            'plan_id'        => ['nullable', 'integer', 'exists:subscription_plans,id'],
        ]);

        $exit = Artisan::call('tenant:create', [
            'name'             => $data['name'],
            'subdomain'        => $data['subdomain'],
            '--db-name'        => $data['db_name'],
            '--db-user'        => $data['db_user'] ?? null,
            '--db-password'    => $data['db_password'] ?? null,
            '--admin-email'    => $data['admin_email'],
            '--admin-name'     => $data['admin_name'],
            '--admin-password' => $data['admin_password'],
        ]);

        if ($exit !== 0) {
            return back()->withInput()->withErrors(['subdomain' => 'Provisioning failed. Check the details and try again.']);
        }

        $tenant = Tenant::where('subdomain', $data['subdomain'])->firstOrFail();
        $tenant->update([
            'contact_email' => $data['admin_email'],
            'contact_phone' => $data['contact_phone'] ?? null,
            'address'       => $data['address'] ?? null,
            'plan_id'       => $data['plan_id'] ?? null,
        ]);

        AuditLog::record('tenant.created', $tenant, [
            'name'      => $tenant->name,
            'subdomain' => $tenant->subdomain,
            'db_name'   => $tenant->db_name,
        ]);

        return redirect()
            ->route('master.tenants.show', $tenant)
            ->with('success', "School '{$tenant->name}' created. Next: run tenant migrations via SSH: php artisan tenant:migrate --subdomain={$tenant->subdomain}");
    }

    public function show(Tenant $tenant): View
    {
        $tenant->load('plan', 'users');

        $stats = [
            'users'     => $tenant->users()->count(),
            'invoices'  => \App\Models\Master\Invoice::where('tenant_id', $tenant->id)->count(),
            'paid'      => \App\Models\Master\Payment::where('tenant_id', $tenant->id)->where('status', 'succeeded')->sum('amount'),
            'tickets'   => \App\Models\Master\SupportTicket::where('tenant_id', $tenant->id)->count(),
        ];

        $recentInvoices = \App\Models\Master\Invoice::where('tenant_id', $tenant->id)
            ->orderByDesc('id')->limit(10)->get();
        $recentPayments = \App\Models\Master\Payment::where('tenant_id', $tenant->id)
            ->orderByDesc('id')->limit(10)->get();
        $recentTickets  = \App\Models\Master\SupportTicket::where('tenant_id', $tenant->id)
            ->orderByDesc('id')->limit(5)->get();

        return view('master.tenants.show', compact('tenant', 'stats', 'recentInvoices', 'recentPayments', 'recentTickets'));
    }

    public function edit(Tenant $tenant): View
    {
        $plans = \App\Models\Master\SubscriptionPlan::orderBy('sort_order')->get();

        return view('master.tenants.edit', compact('tenant', 'plans'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:191'],
            'contact_email'    => ['nullable', 'email', 'max:191'],
            'contact_phone'    => ['nullable', 'string', 'max:30'],
            'address'          => ['nullable', 'string', 'max:500'],
            'plan_id'          => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'subscription_ends_at' => ['nullable', 'date'],
        ]);

        $tenant->update($data);

        AuditLog::record('tenant.updated', $tenant, $data);

        return redirect()->route('master.tenants.index')->with('success', "Tenant '{$tenant->name}' updated.");
    }

    public function suspend(Tenant $tenant): RedirectResponse
    {
        $tenant->update(['status' => 'suspended']);
        AuditLog::record('tenant.suspended', $tenant);

        return back()->with('success', "Tenant '{$tenant->name}' suspended.");
    }

    public function activate(Tenant $tenant): RedirectResponse
    {
        $tenant->update(['status' => 'active']);
        AuditLog::record('tenant.activated', $tenant);

        return back()->with('success', "Tenant '{$tenant->name}' re-activated.");
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $name = $tenant->name;
        $tenant->delete();
        AuditLog::record('tenant.deleted', null, ['name' => $name, 'db_name' => $tenant->db_name]);

        return redirect()->route('master.tenants.index')->with('success', "Tenant '{$name}' deleted. Remember to drop its MySQL database and subdomain in cPanel.");
    }
}
