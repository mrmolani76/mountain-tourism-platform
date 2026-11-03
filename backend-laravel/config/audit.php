<?php

/**
 * owen-it/laravel-auditing — disabled by default until hist tables ship per module.
 * eQMS enables this with per-tenant hist_* tables; Mountain Module 1 uses core_audit_event.
 */

return [
    'enabled' => env('AUDITING_ENABLED', false),

    'implementation' => OwenIt\Auditing\Models\Audit::class,

    'user' => [
        'morph_prefix' => 'user',
        'guards' => ['web'],
        'resolver' => OwenIt\Auditing\Resolvers\UserResolver::class,
    ],

    'resolvers' => [
        'ip_address' => OwenIt\Auditing\Resolvers\IpAddressResolver::class,
        'user_agent' => OwenIt\Auditing\Resolvers\UserAgentResolver::class,
        'url' => OwenIt\Auditing\Resolvers\UrlResolver::class,
    ],

    'events' => [
        'created',
        'updated',
        'deleted',
        'restored',
    ],

    'strict' => false,

    'timestamps' => true,

    'threshold' => 0,

    'driver' => 'database',

    'drivers' => [
        'database' => [
            'table' => 'hist',
            'connection' => null,
        ],
    ],

    'console' => false,
];
