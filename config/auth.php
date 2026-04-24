<?php

return [

    'defaults' => [
        'guard'     => 'vendor',
        'passwords' => 'vendors',
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],
        'vendor' => [
            'driver'   => 'session',
            'provider' => 'vendors',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Vendor::class,
        ],
        'vendors' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Vendor::class,
        ],
    ],

    'passwords' => [
        'vendors' => [
            'provider' => 'vendors',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
