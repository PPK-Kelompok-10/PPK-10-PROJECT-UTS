<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Ini adalah file bootstrap/app.php bawaan Laravel 11.
// Yang PERLU ditambahkan hanya bagian ->withMiddleware(...) di bawah,
// untuk mendaftarkan alias 'role' agar bisa dipakai di routes/web.php
// contoh: ->middleware('role:admin')

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
