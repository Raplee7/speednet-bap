<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// Pastikan ini di-import

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Daftarkan alias 'guest' untuk menggunakan middleware kustom Anda
        $middleware->alias([
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);

        // Pengguna yang TIDAK TEROTENTIKASI mencoba mengakses rute yang butuh login
        // akan diarahkan ke rute bernama 'login' (yaitu GET /login untuk Admin/Kasir)
        $middleware->redirectUsersTo(fn(Request $request) => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
