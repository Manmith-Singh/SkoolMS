<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role-based access middleware.
 *
 * Looks at the user's role in `config/permissions.php` and checks the current
 * route name against the allowed patterns.  Admin roles bypass entirely.
 *
 * Apply with the alias `role.access` (registered in bootstrap/app.php).
 */
class RoleAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('master.login');
        }

        $adminRoles = (array) config('permissions.admin_roles', ['super_admin', 'admin']);
        if (in_array($user->role, $adminRoles, true)) {
            return $next($request);
        }

        $allowed = (array) config("permissions.roles.{$user->role}", []);
        if (empty($allowed)) {
            abort(403, "Your role ({$user->role}) does not have any permissions configured.");
        }

        $current = $request->route() ? $request->route()->getName() : null;
        if (! $current) {
            // Unnamed route — fail closed
            abort(403, 'This page is not accessible to your role.');
        }

        if (! $this->isAllowed($current, $allowed)) {
            abort(403, "Your role ({$user->role}) is not permitted to access this page.");
        }

        return $next($request);
    }

    protected function isAllowed(string $route, array $allowed): bool
    {
        foreach ($allowed as $pattern) {
            if ($pattern === '*') return true;
            if ($pattern === $route) return true;
            if (str_ends_with($pattern, '.*')) {
                $prefix = substr($pattern, 0, -2);
                if (str_starts_with($route, $prefix . '.')) return true;
            }
        }
        return false;
    }
}
