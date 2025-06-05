    <?php

return [
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:8080')),
    'guard' => ['web'],
    'expiration' => null,
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
    'middleware' => [
        'verify_csrf_token' => Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
    ],
];