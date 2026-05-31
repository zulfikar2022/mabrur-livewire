<?php

use App\Http\Middleware\AdminOnlyMiddleware;
use App\Http\Middleware\StatusCheckerMiddleware;
use App\Http\Middleware\SuperAdminOnlyMiddleware;
use App\Http\Middleware\UserOnlyMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.only' => AdminOnlyMiddleware::class,
            'user.only' => UserOnlyMiddleware::class,
            'super-admin.only' => SuperAdminOnlyMiddleware::class,
            'status' => StatusCheckerMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
