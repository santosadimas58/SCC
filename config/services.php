<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'scc' => [
        'api_token' => env('SCC_API_TOKEN'),
    ],

    'bmkg_weather' => [
        'adm4' => env('BMKG_WEATHER_ADM4', '32.73.08.1003'),
        'display_name' => env('BMKG_WEATHER_DISPLAY', 'Setiabudhi, Bandung'),
        'source_name' => env('BMKG_WEATHER_SOURCE', 'Ledeng, Cidadap, Kota Bandung'),
        'cache_seconds' => env('BMKG_WEATHER_CACHE_SECONDS', 1800),
        'auto_simulation' => env('BMKG_WEATHER_AUTO_SIMULATION', true),
        'simulation_interval_seconds' => env('BMKG_WEATHER_SIMULATION_INTERVAL_SECONDS', 60),
        'demo_mode' => env('BMKG_WEATHER_DEMO_MODE', false),
        'demo_min_solar_factor' => env('BMKG_WEATHER_DEMO_MIN_SOLAR_FACTOR', 0.62),
        'demo_soc_delta_multiplier' => env('BMKG_WEATHER_DEMO_SOC_DELTA_MULTIPLIER', 5.0),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
