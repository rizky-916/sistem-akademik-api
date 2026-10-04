<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Options
    |--------------------------------------------------------------------------
    |
    | The settings below determine the behavior of cross-origin requests in your
    | application. You can modify these settings as needed to allow or restrict
    | which domains are permitted to access your application via AJAX requests.
    |
    */

    'paths' => ['api/*'], // Mengatur agar hanya API yang diizinkan

    'allowed_methods' => ['*'], // Mengizinkan semua metode HTTP (GET, POST, PUT, DELETE, dll)

    // Daftar origin frontend dipisah koma, contoh: CORS_ALLOWED_ORIGINS=http://localhost:3000,https://app.domain.com
    'allowed_origins' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Mengizinkan semua header

    'exposed_headers' => [],

    'max_age' => 0, // Durasi cache CORS dalam detik

    'supports_credentials' => true, // Mengizinkan cookie/kredensial dari origin di atas
];
