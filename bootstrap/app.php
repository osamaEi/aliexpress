<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SetCurrency::class,
            \App\Http\Middleware\SellerAccessControl::class,
            \App\Http\Middleware\DistributorAccessControl::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'distributor' => \App\Http\Middleware\IsDistributor::class,
            'seller.access' => \App\Http\Middleware\SellerAccessControl::class,
        ]);
        // Exclude webhooks from CSRF verification
        $middleware->validateCsrfTokens(except: [
            '/paymob/webhook',
            '/webhooks/aliexpress/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
