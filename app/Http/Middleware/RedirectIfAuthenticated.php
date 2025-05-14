<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Tentukan path redirect berdasarkan guard
                $redirectTo = match ($guard) {
                    'customer_web' => route('customer.dashboard'), // Untuk pelanggan
                    default => route('dashboard'),                 // Untuk admin/kasir (guard 'web' default)
                                                                   // 'dashboard' akan mengarah ke /dashboard (admin)
                };
                return redirect($redirectTo);
            }
        }
        return $next($request);
    }
}
