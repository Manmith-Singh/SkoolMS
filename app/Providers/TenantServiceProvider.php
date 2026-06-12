<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class TenantServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapMasterRoutes();
        $this->mapTenantRoutes();
    }

    protected function mapMasterRoutes(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/master.php'));
    }

    protected function mapTenantRoutes(): void
    {
        Route::middleware(['web', 'auth', 'tenant.access', 'role.access'])
            ->group(base_path('routes/tenant.php'));
    }
}
