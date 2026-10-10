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
    ->withMiddleware(function (Middleware $middleware) {
        // ... middleware kamu yang sudah ada ...
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    // TAMBAHKAN BLOK INI DI PALING BAWAH UNTUK VERCEL
    ->useStoragePath(isset($_ENV['VERCEL']) ? '/tmp/storage' : storage_path())
    ->create();
