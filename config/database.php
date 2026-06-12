<?php

return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver'         => 'mysql',
            'url'            => env('DB_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '3306'),
            'database'       => env('DB_DATABASE', 'schoolms_master'),
            'username'       => env('DB_USERNAME', 'schoolms_app'),
            'password'       => env('DB_PASSWORD', ''),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_ci',
            'prefix'         => '',
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => 'InnoDB',
        ],

        /*
        |--------------------------------------------------------------------------
        | Tenant template connection
        |--------------------------------------------------------------------------
        | Used as the source-of-truth for the schema every newly provisioned
        | tenant is seeded with.  Only used during `tenant:create`.
        */
        'tenant_template' => [
            'driver'         => 'mysql',
            'url'            => env('DB_URL'),
            'host'           => env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port'           => env('TENANT_DB_PORT', env('DB_PORT', '3306')),
            'database'       => env('TENANT_DB_DATABASE', 'schoolms_tenant_template'),
            'username'       => env('TENANT_DB_USERNAME', env('DB_USERNAME', 'schoolms_app')),
            'password'       => env('TENANT_DB_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_ci',
            'prefix'         => '',
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => 'InnoDB',
        ],

        /*
        |--------------------------------------------------------------------------
        | Tenant connection (dynamic)
        |--------------------------------------------------------------------------
        | The `database`, `username`, and `password` are reconfigured on every
        | request that lands on a tenant subdomain.  Defaults below are used
        | as a fallback if you ever query the tenant connection outside a
        | subdomain request (e.g. during `php artisan tenant:migrate`).
        */
        'tenant' => [
            'driver'         => 'mysql',
            'url'            => env('DB_URL'),
            'host'           => env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port'           => env('TENANT_DB_PORT', env('DB_PORT', '3306')),
            'database'       => env('TENANT_DB_DATABASE', 'schoolms_tenant_template'),
            'username'       => env('TENANT_DB_USERNAME', env('DB_USERNAME', 'schoolms_app')),
            'password'       => env('TENANT_DB_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_ci',
            'prefix'         => '',
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => 'InnoDB',
        ],

        'pgsql' => [
            'driver'   => 'pgsql',
            'url'      => env('DB_URL'),
            'host'     => env('DB_HOST', '127.0.0.1'),
            'port'     => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset'  => env('DB_CHARSET', 'utf8'),
            'prefix'   => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix'  => env('REDIS_PREFIX', 'schoolms_database_'),
        ],

        'default' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

    ],

];
