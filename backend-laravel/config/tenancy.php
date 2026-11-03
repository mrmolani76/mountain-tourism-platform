<?php

// Tenancy configuration ported from Django `backend/tenancy/` (see
// docs/LARAVEL_SWITCH_AUDIT.md §1 — DB-per-tenant, host → Domain registry).

return [
    // Comma-separated central (landlord) hosts — these skip tenant resolution,
    // mirroring TENANCY_CENTRAL_HOSTS / TENANCY_CENTRAL_PREFIX in Django.
    'central_hosts' => array_filter(array_map(
        'trim',
        explode(',', (string) env('TENANCY_CENTRAL_HOSTS', 'localhost,127.0.0.1'))
    )),

    // Physical tenant databases must be provisioned by ops (same policy as
    // Django: physical createdb is deliberate ops work, never app code).
    // The `tenant` runtime connection is assembled from this template at
    // request time by App\Http\Middleware\ResolveTenant.
    'connection_name' => 'tenant',
];
