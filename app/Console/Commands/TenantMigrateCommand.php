<?php

namespace App\Console\Commands;

use App\Models\Master\Tenant;
use App\Services\TenantDatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantMigrateCommand extends Command
{
    protected $signature = 'tenant:migrate
        {--subdomain= : Subdomain to migrate (omit to migrate every active tenant)}
        {--fresh : Drop all tables and re-run}
        {--seed : Run TenantSeeder after migrating}';

    protected $description = 'Run tenant migrations against one or all tenant databases.';

    public function handle(TenantDatabaseManager $manager): int
    {
        $subdomain = $this->option('subdomain');
        $fresh     = $this->option('fresh');
        $seed      = $this->option('seed');

        $tenants = $subdomain
            ? Tenant::where('subdomain', $subdomain)->get()
            : Tenant::where('status', 'active')->get();

        if ($tenants->isEmpty()) {
            $this->warn('No matching tenants.');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $this->info("→ Migrating {$tenant->subdomain} ({$tenant->db_name})");

            $manager->switchConnection($tenant);

            if ($fresh) {
                Artisan::call('migrate:fresh', [
                    '--database' => 'tenant',
                    '--path'     => 'database/migrations/tenant',
                    '--force'    => true,
                ]);
            } else {
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path'     => 'database/migrations/tenant',
                    '--force'    => true,
                ]);
            }
            $this->line(Artisan::output());

            if ($seed) {
                Artisan::call('db:seed', [
                    '--class'    => 'Database\\Seeders\\TenantSeeder',
                    '--database' => 'tenant',
                    '--force'    => true,
                ]);
                $this->line(Artisan::output());
            }
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
