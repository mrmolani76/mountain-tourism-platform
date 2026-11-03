<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Frontend origin: set FRONTEND_URL (e.g. http://lab.localhost:3000).
    'allowed_origins' => array_values(array_filter([env('FRONTEND_URL', 'http://localhost:3000')], fn ($v) => $v !== '' && $v !== '*')),

    'allowed_origins_patterns' => [
        '#^https?://([a-z0-9-]+\.)?localhost(:\d+)?$#',
        '#^https?://127\.0\.0\.1(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Required for Nuxt SPA session cookies (credentials: include).
    'supports_credentials' => true,

];
