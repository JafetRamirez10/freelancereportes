<?php

declare(strict_types=1);

return [

    'currency' => 'USD',
    'currency_symbol' => '$',
    'admin_email' => env('SEED_ADMIN_EMAIL', 'info@jramirezr.com'),
    'display_timezone' => env('APP_TIMEZONE', 'UTC'),

    'evidence' => [
        'disk' => 'local',
        'sales_directory' => 'evidence/sales',
        'purchases_directory' => 'evidence/purchases',
        'max_kb' => 5120,
        'mimes' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/pdf',
        ],
        'extensions' => ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
    ],

    'dashboard' => [
        'cache_seconds' => 300,
        'recent_sales_limit' => 10,
    ],

    'reminders' => [
        'month_days' => 30,
        'week_days' => 7,
    ],

    'seed' => [
        'admin_name' => env('SEED_ADMIN_NAME', 'Administrador'),
        'admin_email' => env('SEED_ADMIN_EMAIL', 'info@jramirezr.com'),
        'admin_password' => env('SEED_ADMIN_PASSWORD'),
    ],

];
