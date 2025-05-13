<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\DeviceMiddleware;
use App\Http\Middleware\ConsumenMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/status/health',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.consumen' => ConsumenMiddleware::class,
            'auth.admin'    => AdminMiddleware::class,
            'auth.device'   => DeviceMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
