<?php

return [
    'defaults'         => [
        'guard'     => 'web', // Guard default untuk Admin/Kasir (User model)
        'passwords' => 'users',
    ],

    'guards'           => [
        'web'          => [ // Guard untuk Admin/Kasir (User Model) - Ini sudah ada
            'driver'   => 'session',
            'provider' => 'users',
        ],
        'customer_web' => [ // TAMBAHKAN GUARD BARU UNTUK PELANGGAN
            'driver'   => 'session',
            'provider' => 'customers_provider', // Nama provider baru untuk pelanggan
        ],
        // 'sanctum' => [ ... ], // Biarkan jika ada
    ],

    'providers'        => [
        'users'              => [ // Provider untuk Admin/Kasir (User Model) - Ini sudah ada
            'driver' => 'eloquent',
            'model'  => App\Models\User::class,
        ],
        'customers_provider' => [ // TAMBAHKAN PROVIDER BARU UNTUK PELANGGAN
            'driver' => 'eloquent',
            'model'  => App\Models\Customer::class, // Pastikan path ke model Customer Anda benar
        ],
    ],

    'passwords'        => [
        'users' => [ // Untuk Admin/Kasir (User Model) - Ini sudah ada
            'provider' => 'users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
        // 'customers' => [ ... ], // Bisa ditambahkan nanti jika perlu reset password pelanggan
    ],

    'password_timeout' => 10800,
];
