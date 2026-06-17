<?php

namespace App\Console\Commands;

use App\Models\Master\AuditLog;
use App\Models\Master\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SuspendExpiredTenants extends Command
{
    protected $signature = 'tenants:auto-suspend';

    protected $description = 'Suspend tenants whose subscription has expired.';

    public function handle(): int
    {
        $expired = Tenant::where('status', 'active')
            ->whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '<', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired tenants found.');

            return self::SUCCESS;
        }

        foreach ($expired as $tenant) {
            $tenant->update(['status' => 'suspended']);

            Cache::forget('tenant:subdomain:' . $tenant->subdomain);

            AuditLog::record('tenant.auto_suspended', $tenant, [
                'name'       => $tenant->name,
                'subdomain'  => $tenant->subdomain,
                'expired_at' => $tenant->subscription_ends_at?->toDateTimeString(),
            ]);

            $this->line("Suspended: {$tenant->name} ({$tenant->subdomain})");
        }

        $this->info("Suspended {$expired->count()} expired tenant(s).");

        return self::SUCCESS;
    }
}
