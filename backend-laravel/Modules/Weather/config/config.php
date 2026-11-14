<?php

return [
    'name' => 'Weather',

    /*
    | MET Norway Locationforecast requires a descriptive User-Agent.
    | Override via WEATHER_MET_USER_AGENT in .env if needed.
    */
    'met_user_agent' => env(
        'WEATHER_MET_USER_AGENT',
        'MountainTourismPlatform/1.0 (weather module; contact@example.com)'
    ),

    'met_base_url' => env(
        'WEATHER_MET_BASE_URL',
        'https://api.met.no/weatherapi/locationforecast/2.0/compact'
    ),

    'open_meteo_base_url' => env(
        'WEATHER_OPEN_METEO_BASE_URL',
        'https://api.open-meteo.com/v1/forecast'
    ),

    'http_timeout_seconds' => (int) env('WEATHER_HTTP_TIMEOUT', 12),

    'attribution' => 'Weather data from MET Norway',
];
