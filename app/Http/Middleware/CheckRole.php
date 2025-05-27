<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Jangan lupa import Auth

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  // Kita akan terima parameter peran di sini
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Cek apakah pengguna sudah login
        if (! Auth::check()) {
            // Jika belum login, arahkan ke halaman login (sesuaikan dengan nama rute login admin/staff kamu)
            return redirect()->route('login');
        }

        // Dapatkan pengguna yang sedang login
        $user = Auth::user();

        // Cek apakah pengguna memiliki salah satu peran yang diizinkan
        foreach ($roles as $role) {
            // Asumsi kamu punya kolom 'role' di model User
            // dan nilainya 'admin', 'cashier', dll.
            if ($user->role == $role) {
                return $next($request); // Izinkan akses jika peran sesuai
            }
        }

        // Jika tidak ada peran yang sesuai, bisa arahkan ke halaman error atau dashboard dengan pesan
        // Misalnya, kembali ke halaman sebelumnya dengan pesan error
        // return back()->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        // Atau, tampilkan halaman 403 Forbidden
        abort(403, 'ANDA TIDAK MEMILIKI AKSES.');
    }
}
