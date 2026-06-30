<?php

namespace App\Console\Commands;

use App\Models\Master\Tenant;
use App\Models\Master\User;
use App\Models\Tenant\Scopes\AcademicYearScope;
use App\Services\TenantDatabaseManager;
use Database\Seeders\Wipe\TenantBaselineSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class WipeDataCommand extends Command
{
    protected $signature = 'db:wipe-data
        {--force : Skip confirmation prompt}';

    protected $description = 'Truncate all data except superadmin/admin users (master) and all data per tenant, then re-seed baseline.';

    private const TENANT_TABLES = [
        'academic_years', 'attendance', 'classes', 'exam_class', 'exam_subject',
        'exam_types', 'exams', 'expenditure_transactions', 'expenditure_types',
        'fee_categories', 'fee_payments', 'fees', 'income_transactions', 'income_types',
        'payrolls', 'results', 'staff_attendance', 'student_enrollments', 'students',
        'subject_class', 'subjects', 'teacher_increments', 'teacher_subject', 'teachers',
    ];

    private const MASTER_TABLES_TO_TRUNCATE = [
        'subscriptions', 'invoices', 'invoice_items', 'payments',
        'support_tickets', 'support_ticket_replies', 'audit_logs', 'api_keys',
        'password_reset_tokens', 'sessions', 'cache', 'cache_locks',
    ];

    public function handle(TenantDatabaseManager $manager): int
    {
        if (! $this->option('force')) {
            $confirmed = $this->confirm(
                'This will DELETE all data except admin users in the master DB and ALL data in every tenant database. Are you sure?',
                false
            );
            if (! $confirmed) {
                $this->info('Cancelled.');

                return self::SUCCESS;
            }
        }

        $this->warn('Starting data wipe...');

        // ─── MASTER DB ───────────────────────────────────────────────
        $this->info('── Master Database ──');
        $this->wipeMaster();

        // ─── TENANT DBS ──────────────────────────────────────────────
        $tenants = Tenant::where('status', 'active')->get();

        if ($tenants->isEmpty()) {
            $this->warn('No active tenants found.');
        } else {
            $this->info('→ Running pending tenant migrations first...');
            Artisan::call('tenant:migrate');
            $this->line(Artisan::output());

            foreach ($tenants as $tenant) {
                $this->info("→ Tenant: {$tenant->name} ({$tenant->subdomain})");
                $manager->switchConnection($tenant);
                $this->wipeTenant();
                $this->seedTenant();
            }
        }

        $this->newLine();
        $this->info('✓ Data wipe complete.');
        $this->line('Admin users preserved. All other data truncated and baseline re-seeded.');

        return self::SUCCESS;
    }

    private function wipeMaster(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $deleted = User::whereNotIn('role', ['super_admin', 'admin'])->delete();
        $this->line("  Deleted {$deleted} non-admin user(s).");

        foreach (self::MASTER_TABLES_TO_TRUNCATE as $table) {
            DB::table($table)->truncate();
            $this->line("  Truncated: {$table}");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function wipeTenant(): void
    {
        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0');

        $existing = DB::connection('tenant')->table('information_schema.tables')
            ->where('table_schema', DB::connection('tenant')->getDatabaseName())
            ->whereNotIn('table_name', ['migrations'])
            ->pluck('table_name')
            ->all();

        $tables = array_intersect(self::TENANT_TABLES, $existing);
        $skipped = array_diff(self::TENANT_TABLES, $existing);

        foreach ($tables as $table) {
            DB::connection('tenant')->table($table)->truncate();
        }

        $this->line('  Truncated ' . count($tables) . ' table(s).' . ($skipped ? ' Skipped (missing): ' . implode(', ', $skipped) : ''));

        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function seedTenant(): void
    {
        $this->line('  Seeding baseline data...');

        AcademicYearScope::disable();

        Artisan::call('db:seed', [
            '--class' => TenantBaselineSeeder::class,
            '--database' => 'tenant',
            '--force' => true,
        ]);

        AcademicYearScope::enable();
    }
}
