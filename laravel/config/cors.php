<?php


return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'], // Add storage/* for images
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:8080'], // Specify your frontend origin
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // Required for Sanctum
];