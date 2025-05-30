<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request; // <-- Facade penting untuk reset password
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str; // Event setelah password direset
use Illuminate\Validation\Rules;

// Pastikan model User di-import

class NewPasswordController extends Controller
{
    /**
     * Menampilkan view form reset password baru.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // Kita akan buat view ini nanti: resources/views/auth/reset-password.blade.php
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Menangani permintaan reset password baru.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::min(3)],
        ], [
            'token.required'     => 'Token reset password tidak valid.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format alamat email tidak valid.',
            'password.required'  => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min'       => 'Password baru minimal :min karakter.',
            // Tambahkan pesan lain jika perlu sesuai aturan validasi Password
        ]);

        // Mencoba mereset password user.
        // Argumen ketiga dari 'broker' adalah nama broker password, defaultnya 'users'.
        $status = Password::broker(config('fortify.passwords', 'users'))->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                                      // Callback ini akan dijalankan jika token dan email valid
                /** @var User $user */// Type hint
                $user->forceFill([
                    'password'       => $password,       // Password sudah di-hash oleh trait di model
                    'remember_token' => Str::random(60), // Opsional: reset juga remember token
                ])->save();

                // Opsional: Kirim event bahwa password telah direset
                event(new PasswordReset($user));
            }
        );

        // Jika password berhasil direset
        if ($status == Password::PASSWORD_RESET) {
            // Arahkan ke halaman login dengan pesan sukses
            // Sesuaikan 'login' dengan nama rute login admin/kasir kamu
            return redirect()->route('login')->with('status', __($status));
        }

        // Jika gagal (misalnya token tidak valid atau email tidak cocok)
        // `Password::broker()->reset` akan melempar ValidationException jika email tidak ditemukan atau token salah,
        // jadi kita bisa menangkapnya atau membiarkannya ditangani oleh handler default Laravel.
        // Namun, jika ada pesan status lain dari broker, kita bisa menampilkannya.
        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
