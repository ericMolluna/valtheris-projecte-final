<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })

    ->withProviders([
        App\Providers\RouteServiceProvider::class,
        // Otros proveedores
    ])
    
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
