<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),   // front (clientes)
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        // Guard para CLIENTE (front)
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Guard para ADMIN (panel Filament)
        'admin' => [
            'driver' => 'session',
            'provider' => 'users',  // misma tabla users
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => env('AUTH_MODEL', App\Models\User::class),
        ],
        // si algún día usas otra tabla/modelo, aquí lo agregas
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
