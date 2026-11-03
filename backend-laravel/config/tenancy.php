<?php

/**
 * Tenancy configuration — InventorySystem-docker (stancl) shape adapted for Mountain Tourism.
 * Runtime today uses App\Tenancy\* bridge (item 70 parity). After `composer install`,
 * stancl/tenancy DomainTenantResolver can replace the bridge without changing host contract.
 */

return [
    'tenant_model' => \App\Models\Tenant::class,
    'domain_model' => \App\Models\Domain::class,

    'central_domains' => array_values(array_unique(array_filter(array_merge(
        array_map('trim', explode(',', (string) env('TENANCY_CENTRAL_HOSTS', 'localhost,127.0.0.1'))),
        [
            env('CENTRAL_DOMAIN'),
            env('CENTRAL_API_DOMAIN'),
            env('SAAS_DOMAIN'),
        ]
    )))),

    // Hostnames matching this pattern are always central (SaaS panel).
    'central_host_pattern' => '/^saas\\./',

    'connection_name' => 'tenant',
    'central_connection' => env('DB_CONNECTION', 'pgsql'),

    'database' => [
        'prefix' => env('TENANCY_DB_PREFIX', ''),
        'suffix' => '',
        // Physical DB create remains ops (same policy as Django).
        'managers' => [
            'pgsql' => 'Stancl\\Tenancy\\TenantDatabaseManagers\\PostgreSQLDatabaseManager',
        ],
    ],

    'bootstrappers' => [
        'Stancl\\Tenancy\\Bootstrappers\\DatabaseTenancyBootstrapper',
        'Stancl\\Tenancy\\Bootstrappers\\CacheTenancyBootstrapper',
        'Stancl\\Tenancy\\Bootstrappers\\FilesystemTenancyBootstrapper',
        'Stancl\\Tenancy\\Bootstrappers\\QueueTenancyBootstrapper',
    ],

    /**
     * eQMS Complere pattern: tenant migrations are module-wise under
     * Modules/{Name}/Database/migrations/tenant/ (+ optional root tenant path).
     */
    'migration_parameters' => [
        '--force' => true,
        '--path' => array_values(array_filter(array_merge(
            glob(database_path('migrations/tenant')) ?: [],
            glob(base_path('Modules/*/Database/migrations/tenant')) ?: [],
        ))),
        '--realpath' => true,
    ],
];
