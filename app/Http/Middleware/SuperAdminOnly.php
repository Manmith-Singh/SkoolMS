<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates a route so that only authenticated SuperAdmins can pass.
 * The apex (apex = no tenant) routes in routes/master.php use this
 * middleware to block tenant admins from poking at the SaaS control
 * plane.
 */
class SuperAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('master.login');
        }

        if (! $user->isSuperAdmin()) {
            abort(403, 'Super-admin access required.');
        }

        // Super-admins must not have a tenant_id; if they do it's a misconfig
        if ($user->tenant_id) {
            abort(403, 'Super-admins cannot be tied to a tenant.');
        }

        return $next($request);
    }
}
