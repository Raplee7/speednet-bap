<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request; // Pastikan ini di-use
use Illuminate\Support\Facades\Route as RouteFacade;

// Untuk mengecek nama rute

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Cek apakah rute yang sedang diakses adalah bagian dari grup rute 'customer.'
            // atau apakah URL-nya diawali dengan '/pelanggan'
            if ($request->is('pelanggan/*') || (RouteFacade::currentRouteName() && str_starts_with(RouteFacade::currentRouteName(), 'customer.'))) {
                // Jika ini adalah area pelanggan, arahkan ke rute login pelanggan
                // Rute 'customer.login.form' akan redirect ke landing page dengan flag buka modal
                return route('customer.login.form');
            }

            // Jika bukan area pelanggan, arahkan ke rute login admin default
            // Rute 'login' Anda mengarah ke form login admin (/login)
            return route('login');
        }
        return null; // Untuk request JSON, biasanya tidak ada redirect, tapi kirim response 401
    }
}
