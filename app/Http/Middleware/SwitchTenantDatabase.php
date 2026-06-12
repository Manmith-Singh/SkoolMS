<?php

namespace App\Http\Middleware;

use App\Services\TenantDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SwitchTenantDatabase
{
    public function __construct(protected TenantDatabaseManager $manager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if (! $tenant) {
            // Apex domain — keep using master connection.
            return $next($request);
        }

        $this->manager->switchConnection($tenant);

        return $next($request);
    }
}
