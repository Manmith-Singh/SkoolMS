<?php

namespace App\Console\Commands;

use App\Models\Master\Tenant;
use App\Models\Master\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateTenantCommand extends Command
{
    protected $signature = 'tenant:create
        {name : School name (e.g. "Greenfield Academy")}
        {subdomain : Subdomain slug (e.g. greenfield)}
        {--db-name= : Exact MySQL database name (required on shared hosting)}
        {--db-user= : MySQL username for this tenant DB}
        {--db-password= : MySQL password for the tenant DB user}
        {--admin-email= : Email for the school admin}
        {--admin-name= : Name for the school admin}
        {--admin-password= : Password for the school admin (random if omitted)}';

    protected $description = 'Create tenant record + admin user. Database and subdomain must be pre-created manually.';

    public function handle(): int
    {
        $name      = (string) $this->argument('name');
        $subdomain = strtolower((string) $this->argument('subdomain'));
        $dbName    = $this->option('db-name');

        $validator = Validator::make([
            'name'      => $name,
            'subdomain' => $subdomain,
            'db_name'   => $dbName,
        ], [
            'name'      => ['required', 'string', 'max:191'],
            'subdomain' => ['required', 'string', 'max:63', 'regex:/' . config('tenancy.subdomain_pattern') . '/'],
            'db_name'   => ['required', 'string', 'max:64'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $e) {
                $this->error($e);
            }
            return self::FAILURE;
        }

        if (Tenant::where('subdomain', $subdomain)->exists()) {
            $this->error("A school with subdomain '{$subdomain}' already exists.");
            return self::FAILURE;
        }

        if (Tenant::where('db_name', $dbName)->exists()) {
            $this->error("A school with database '{$dbName}' already exists.");
            return self::FAILURE;
        }

        $adminEmail    = $this->option('admin-email') ?: "admin@{$subdomain}." . config('tenancy.domain_base');
        $adminName     = $this->option('admin-name') ?: "{$name} Admin";
        $adminPassword = $this->option('admin-password') ?: Str::random(16);
        $dbUser        = $this->option('db-user');
        $dbPass        = $this->option('db-password');

        // Create tenant record (DB must already exist in MySQL)
        $tenant = Tenant::create([
            'name'          => $name,
            'subdomain'     => $subdomain,
            'db_name'       => $dbName,
            'db_username'   => $dbUser,
            'db_password'   => $dbPass,
            'status'        => 'active',
            'contact_email' => $adminEmail,
            'trial_ends_at' => now()->addDays(30),
        ]);
        $this->info("Tenant record created (id={$tenant->id}, db={$dbName}).");

        // Create admin user in master DB
        User::create([
            'tenant_id' => $tenant->id,
            'name'      => $adminName,
            'email'     => $adminEmail,
            'password'  => Hash::make($adminPassword),
            'role'      => 'admin',
        ]);

        $this->newLine();
        $this->info('=== Tenant record created ===');
        $this->table(
            ['Field', 'Value'],
            [
                ['School',        $tenant->name],
                ['Subdomain',     $tenant->subdomain],
                ['URL',           $tenant->url()],
                ['Database',      $tenant->db_name],
                ['DB user',       $tenant->db_username ?? '(shared)'],
                ['Admin email',   $adminEmail],
                ['Admin pass',    $adminPassword],
            ]
        );
        $this->newLine();
        $this->warn('NEXT STEPS (manual):');
        $this->line('  1. Create MySQL database "' . $dbName . '" in cPanel (done? skip).');
        $this->line('  2. Create subdomain "' . $subdomain . '.' . config('tenancy.app_domain') . '" in cPanel (done? skip).');
        $this->line('  3. Run: php artisan tenant:migrate --subdomain=' . $subdomain);
        $this->line('  4. Optionally seed: php artisan tinker ... (run TenantSeeder manually)');
        $this->newLine();

        return self::SUCCESS;
    }
}
