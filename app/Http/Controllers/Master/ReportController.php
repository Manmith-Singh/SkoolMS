<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\AuditLog;
use App\Models\Master\Invoice;
use App\Models\Master\Payment;
use App\Models\Master\SubscriptionPlan;
use App\Models\Master\Tenant;
use App\Models\Master\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $stats = [
            'tenants'           => Tenant::count(),
            'active_tenants'    => Tenant::where('status', 'active')->count(),
            'users'             => User::count(),
            'super_admins'      => User::where('role', 'super_admin')->count(),
            'plans'             => SubscriptionPlan::where('is_active', true)->count(),
            'invoices'          => Invoice::count(),
            'payments'          => Payment::count(),
            'mrr'               => $this->estimateMrr(),
        ];

        // Last 12 months: invoices count + payments revenue
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end   = (clone $start)->endOfMonth();
            $months[] = [
                'label'     => $start->format('M Y'),
                'invoices'  => Invoice::whereBetween('issue_date', [$start, $end])->count(),
                'revenue'   => (float) Payment::where('status', 'succeeded')
                                    ->whereBetween('paid_at', [$start, $end])
                                    ->sum('amount'),
            ];
        }

        // Per-tenant usage (rough proxy: count of users per tenant)
        $tenantUsage = Tenant::withCount('users')->orderByDesc('users_count')->limit(10)->get();

        return view('master.reports.index', compact('stats', 'months', 'tenantUsage'));
    }

    public function revenue(): View
    {
        $rows = Payment::with('tenant')
            ->where('status', 'succeeded')
            ->orderByDesc('paid_at')
            ->paginate(50);

        $totals = [
            'all'    => (float) Payment::where('status', 'succeeded')->sum('amount'),
            'year'   => (float) Payment::where('status', 'succeeded')->where('paid_at', '>=', now()->subYear())->sum('amount'),
            'month'  => (float) Payment::where('status', 'succeeded')->where('paid_at', '>=', now()->subMonth())->sum('amount'),
        ];

        return view('master.reports.revenue', compact('rows', 'totals'));
    }

    private function estimateMrr(): float
    {
        // Naive MRR: sum of plan prices for tenants with active subs
        return (float) Tenant::where('status', 'active')
            ->whereNotNull('plan_id')
            ->join('subscription_plans', 'tenants.plan_id', '=', 'subscription_plans.id')
            ->sum('subscription_plans.price');
    }
}
