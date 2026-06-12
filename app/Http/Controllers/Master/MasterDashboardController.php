<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\AuditLog;
use App\Models\Master\Invoice;
use App\Models\Master\Payment;
use App\Models\Master\SupportTicket;
use App\Models\Master\Tenant;
use App\Models\Master\User;
use Illuminate\View\View;

class MasterDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'tenants'        => Tenant::count(),
            'active'         => Tenant::where('status', 'active')->count(),
            'suspended'      => Tenant::where('status', 'suspended')->count(),
            'trialing'       => Tenant::where('status', 'trial')->count(),
            'users'          => User::count(),
            'super_admins'   => User::where('role', 'super_admin')->count(),
            'open_tickets'   => SupportTicket::whereIn('status', ['open', 'in_progress', 'waiting'])->count(),
            'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
            'mrr'            => (float) Tenant::where('status', 'active')
                                    ->whereNotNull('plan_id')
                                    ->join('subscription_plans', 'tenants.plan_id', '=', 'subscription_plans.id')
                                    ->sum('subscription_plans.price'),
            'revenue_ytd'    => (float) Payment::where('status', 'succeeded')
                                    ->where('paid_at', '>=', now()->startOfYear())
                                    ->sum('amount'),
        ];

        $recentTenants = Tenant::orderByDesc('id')->limit(8)->get();
        $recentTickets = SupportTicket::with('tenant')->orderByDesc('id')->limit(8)->get();
        $recentInvoices = Invoice::with('tenant')->orderByDesc('id')->limit(8)->get();
        $recentAudit    = AuditLog::with('user')->orderByDesc('id')->limit(10)->get();

        return view('master.dashboard', compact(
            'stats', 'recentTenants', 'recentTickets', 'recentInvoices', 'recentAudit'
        ));
    }
}
