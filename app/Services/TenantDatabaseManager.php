<?php

namespace App\Services;

use App\Models\Master\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantDatabaseManager
{
    public function __construct() {}

    /**
     * Extract the subdomain part from a host string.
     * `greenfield.school.test`  =>  `greenfield`
     * `school.test`             =>  null
     * `greenfield.localhost`    =>  `greenfield`
     */
    public function subdomainFromHost(?string $host): ?string
    {
        if (! $host) {
            return null;
        }

        $host = strtolower(trim($host));
        $appDomain = strtolower((string) config('tenancy.app_domain'));

        // `greenfield.school.test` → strip the app domain
        if ($appDomain && str_ends_with($host, $appDomain)) {
            $sub = substr($host, 0, -strlen($appDomain));
            $sub = rtrim($sub, '.');

            return $sub !== '' ? $sub : null;
        }

        // `localhost` or `127.0.0.1` — try first segment of dotted host
        $parts = explode('.', $host);

        if (count($parts) >= 2 && $parts[0] !== 'www' && $parts[0] !== 'schoolms') {
            return $parts[0];
        }

        return null;
    }

    /**
     * Look up a tenant row in the master DB by subdomain.  Cached.
     */
    public function findBySubdomain(string $subdomain): ?Tenant
    {
        $cacheKey = 'tenant:subdomain:' . strtolower($subdomain);

        return Cache::remember($cacheKey, (int) config('tenancy.cache_ttl', 600), function () use ($subdomain) {
            return Tenant::query()
                ->where('subdomain', strtolower($subdomain))
                ->where('status', 'active')
                ->first();
        });
    }

    /**
     * Forcefully clear the cached tenant lookup (e.g. after a status change).
     */
    public function forgetCache(string $subdomain): void
    {
        Cache::forget('tenant:subdomain:' . strtolower($subdomain));
    }

    /**
     * Switch the dynamic `tenant` DB connection to point at the tenant's DB.
     */
    public function switchConnection(Tenant $tenant): void
    {
        // Read the static parts (host, port, charset, etc.) from the existing
        // `tenant` connection defined in config/database.php, then overwrite
        // the per-tenant fields with the row from the `tenants` table.
        $base = config('database.connections.tenant');

        // Only honour the tenant's own credentials if they look like real
        // ones (non-empty strings).  Empty / null values fall through to the
        // single shared user defined in the static connection.
        $dbUser = ! empty($tenant->db_username) ? $tenant->db_username : ($base['username'] ?? null);
        $dbPass = ! empty($tenant->db_password) ? $tenant->db_password : ($base['password'] ?? null);

        Config::set('database.connections.tenant', array_merge($base, [
            'database' => $tenant->db_name,
            'username' => $dbUser,
            'password' => $dbPass,
        ]));

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    /**
     * Provision a brand-new MySQL database for a tenant.
     * Uses DB_ROOT_USERNAME / DB_ROOT_PASSWORD from config.
     */
    public function provisionDatabase(string $databaseName, ?string $username = null, ?string $password = null): void
    {
        $rootUser = config('tenancy.root_username');
        $rootPass = config('tenancy.root_password');

        $host = config('database.connections.tenant.host', '127.0.0.1');
        $port = config('database.connections.tenant.port', '3306');

        $dsn = sprintf('mysql:host=%s;port=%s', $host, $port);

        $pdo = new \PDO($dsn, $rootUser, $rootPass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        if ($username && $password) {
            $pdo->exec("CREATE USER IF NOT EXISTS '{$username}'@'{$host}' IDENTIFIED BY '{$password}'");
            $pdo->exec("GRANT ALL PRIVILEGES ON `{$databaseName}`.* TO '{$username}'@'{$host}'");
            $pdo->exec('FLUSH PRIVILEGES');
        }
    }

    /**
     * Drop a tenant's MySQL database.  Used when an admin deletes a school.
     */
    public function dropDatabase(string $databaseName): void
    {
        $rootUser = config('tenancy.root_username');
        $rootPass = config('tenancy.root_password');

        $host = config('database.connections.tenant.host', '127.0.0.1');
        $port = config('database.connections.tenant.port', '3306');

        $dsn = sprintf('mysql:host=%s;port=%s', $host, $port);

        $pdo = new \PDO($dsn, $rootUser, $rootPass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        $pdo->exec("DROP DATABASE IF EXISTS `{$databaseName}`");
    }
}
