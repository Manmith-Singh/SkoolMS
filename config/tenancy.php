<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Root DB credentials
    |--------------------------------------------------------------------------
    | Used by `tenant:create` to issue CREATE DATABASE statements.  Leave
    | blank on shared hosting and pre-create the DBs manually.
    */

    'root_username' => env('DB_ROOT_USERNAME', 'root'),
    'root_password' => env('DB_ROOT_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Naming conventions
    |--------------------------------------------------------------------------
    | Pattern used when a new school registers:  schoolms_<subdomain>.
    */

    'subdomain_pattern'  => '[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?',
    'domain_base'        => env('TENANT_DOMAIN_BASE', 'school.test'),
    'app_domain'         => env('APP_DOMAIN', 'school.test'),

    /*
    |--------------------------------------------------------------------------
    | Master subdomain
    |--------------------------------------------------------------------------
    | The subdomain where the master (landlord) control plane lives.
    | Tenants at {tenant}.{domain_base} will NOT be looked up when this
    | subdomain is matched (e.g. skoolms.msitsols.com → master, not tenant).
    */

    'master_subdomain' => env('MASTER_SUBDOMAIN', 'skoolms'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    | Cached tenant lookups (in seconds) reduce master DB queries.
    */

    'cache_ttl' => 600,

];
