<?php

namespace App\Http\Middleware;

use App\Models\Master\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the tenant route group.  Run AFTER `IdentifyTenant` and
 * `SwitchTenantDatabase`, so the `currentTenant` binding is already set.
 *
 * Rules:
 *   1. Unauthenticated user → redirect to master login.
 *   2. Super-admin → redirect to master dashboard on the apex domain.
 *   3. Authenticated user with no `tenant_id` (orphan) → log out + login.
 *   4. School user on the apex domain (no subdomain bound) → redirect to
 *      their own tenant subdomain.
 *   5. School user whose `tenant_id` does not match the current tenant
 *      subdomain → 403.
 *   6. Anyone else → allowed.
 */
class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('master.login');
        }

        $user = $request->user();

        // Super-admins belong to the master platform, not any single school.
        if ($user->role === 'super_admin') {
            return redirect()->away(config('app.url') . '/admin');
        }

        // Authenticated user with no tenant assignment (data anomaly).
        if (! $user->tenant_id) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('master.login');
        }

        $currentTenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        // School user hit a tenant route on the apex domain — bounce them
        // over to their own school's subdomain instead of letting them
        // touch the empty template database.
        if (! $currentTenant) {
            $tenant = Tenant::find($user->tenant_id);
            if ($tenant && $tenant->isActive()) {
                return redirect()->away($tenant->url() . $request->getRequestUri());
            }
            abort(404, 'Your school is not available.');
        }

        // Cross-tenant access attempt.
        if ((int) $currentTenant->id !== (int) $user->tenant_id) {
            abort(403, 'You do not have access to this school.');
        }

        return $next($request);
    }
}
