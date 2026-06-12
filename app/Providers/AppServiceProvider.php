<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Avoid MySQL "Specified key was too long" errors on older MySQL/MariaDB.
        Schema::defaultStringLength(191);

        // Force HTTPS for URL generation in production.
        if ($this->app->environment('production')) {
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }
}
