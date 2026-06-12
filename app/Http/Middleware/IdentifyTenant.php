<?php

namespace App\Http\Middleware;

use App\Services\TenantDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(protected TenantDatabaseManager $manager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $this->manager->subdomainFromHost($request->getHost());

        if (! $subdomain) {
            // We are on the main (apex) domain — no tenant context.
            return $next($request);
        }

        // Master subdomain (e.g. skoolms.msitsols.com) — not a tenant.
        if ($subdomain === config('tenancy.master_subdomain')) {
            return $next($request);
        }

        $tenant = $this->manager->findBySubdomain($subdomain);

        if (! $tenant) {
            abort(404, "School '{$subdomain}' not found or inactive.");
        }

        // Make tenant available everywhere for the remainder of the request.
        app()->instance('currentTenant', $tenant);
        View::share('currentTenant', $tenant);

        return $next($request);
    }
}
