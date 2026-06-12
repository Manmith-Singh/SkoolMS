<?php

use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SwitchTenantDatabase;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prependToGroup('web', IdentifyTenant::class);
        $middleware->appendToGroup('web', SwitchTenantDatabase::class);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'role.access' => \App\Http\Middleware\RoleAccess::class,
            'tenant.access' => \App\Http\Middleware\EnsureTenantAccess::class,
            'super_admin' => \App\Http\Middleware\SuperAdminOnly::class,
        ]);

        // Laravel's built-in `Authenticate` middleware calls `route('login')`
        // by default.  We name ours `master.login`, so point it there.
        $middleware->redirectGuestsTo(function () {
            return route('master.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
