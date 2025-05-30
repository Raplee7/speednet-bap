<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Untuk aturan validasi password yang lebih detail
use Illuminate\Validation\Rules\Password;

// Pastikan model User di-import

class UserProfileController extends \Illuminate\Routing\Controller
{
    /**
     * Instantiate a new controller instance.
     * Menerapkan middleware ke semua method di controller ini.
     */
    public function __construct()
    {
        $this->middleware('auth'); // Pastikan hanya user yang sudah login yang bisa akses
                                   // Jika kamu mau lebih spesifik hanya untuk admin/kasir berdasarkan 'role' di middleware routes,
                                   // atau kamu bisa tambahkan pengecekan role di sini jika perlu,
                                   // tapi middleware 'auth' untuk guard 'web' (default) sudah cukup untuk user (admin/kasir).
    }

    /**
     * Menampilkan form untuk mengubah password.
     */
    public function showChangePasswordForm()
    {
        // Kita akan buat view ini di langkah berikutnya
        return view('profile.change-password', [
            'pageTitle' => 'Ubah Password',
        ]);
    }

    /**
     * Memproses permintaan untuk mengubah password.
     */
    public function updatePassword(Request $request)
    {
        /** @var User $user */// Type hint untuk Intelephense agar mengenali $user sebagai model User
        $user = Auth::user();

        // Validasi input
        $request->validate([
            'current_password' => [
                'required',
                // Fungsi kustom untuk mengecek apakah password saat ini benar
                function ($attribute, $value, $fail) use ($user) {
                    if (! Hash::check($value, $user->password)) {
                        $fail('Password saat ini yang Anda masukkan salah.');
                    }
                },
            ],
            'new_password'     => [
                'required',
                'string',
                Password::min(3), // Minimal 8 karakter
                                  // ->mixedCase()    // Harus ada huruf besar dan kecil
                                  // ->numbers()      // Harus ada angka
                                  // ->symbols(),     // Harus ada simbol (opsional, bisa dihapus jika tidak mau)
                'confirmed',      // Harus cocok dengan field 'new_password_confirmation'
            ],
            // Field 'new_password_confirmation' otomatis divalidasi oleh aturan 'confirmed' di 'new_password'
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required'     => 'Password baru wajib diisi.',
            'new_password.min'          => 'Password baru minimal :min karakter.',
            // 'new_password.mixed_case'   => 'Password baru harus mengandung huruf besar dan huruf kecil.',
            // 'new_password.numbers'      => 'Password baru harus mengandung angka.',
            // 'new_password.symbols'      => 'Password baru harus mengandung simbol.',
            'new_password.confirmed'    => 'Konfirmasi password baru tidak cocok.',
        ]);

                                                  // Jika validasi lolos, update password user
                                                  // Model User kita sudah punya cast 'password' => 'hashed', jadi ini akan otomatis di-hash.
        $user->password = $request->new_password; // Atau Hash::make($request->new_password) jika tidak pakai cast 'hashed'
        $user->save();

                                                                 // Beri pesan sukses dan redirect kembali ke form ubah password
                                                                 // Kita akan tampilkan pesan 'status_password' ini di view nanti
        return redirect()->route('profile.change_password.form') // Kita akan buat nama rute ini
            ->with('success', 'Password Anda berhasil diubah! ');
    }
}
