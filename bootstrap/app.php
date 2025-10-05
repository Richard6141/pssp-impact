<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Ajouter le middleware globalement au groupe web
        $middleware->web(append: [
            \App\Http\Middleware\BypassMaintenanceForSuperAdmin::class,
        ]);

        // OU l'ajouter comme middleware nommé si vous préférez
        $middleware->alias([
            'bypass.maintenance' => \App\Http\Middleware\BypassMaintenanceForSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
