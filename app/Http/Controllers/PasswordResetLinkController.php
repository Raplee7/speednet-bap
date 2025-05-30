<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password; // <-- Facade penting untuk reset password
use Illuminate\Validation\ValidationException;

// Untuk menangani error validasi

class PasswordResetLinkController extends Controller
{
    /**
     * Menampilkan view form permintaan link reset password.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Kita akan buat view ini nanti: resources/views/auth/forgot-password.blade.php
        return view('auth.forgot-password');
    }

    /**
     * Menangani permintaan pengiriman link reset password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format alamat email tidak valid.',
        ]);

        // Mengirim link reset password.
        // Argumen kedua dari 'broker' adalah nama broker password, defaultnya 'users'.
        // Ini akan mencari user berdasarkan email di tabel yang dikonfigurasi untuk provider 'users' (biasanya tabel 'users').
        $status = Password::broker(config('fortify.passwords', 'users'))->sendResetLink(
            $request->only('email')
        );

        // Cek status pengiriman link
        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status)); // Pesan sukses dari Laravel
        }

        // Jika email tidak ditemukan atau ada error lain
        throw ValidationException::withMessages([
            'email' => [__($status)], // Pesan error dari Laravel
        ]);
    }
}
