<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomerAccountController extends BaseController
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        // Middleware untuk memastikan hanya pelanggan yang terotentikasi
        // yang bisa mengakses method di controller ini.
        $this->middleware('auth:customer_web');
    }

    /**
     * Menampilkan form untuk mengubah password pelanggan.
     *
     * @return \Illuminate\View\View
     */
    public function showChangePasswordForm()
    {
        $pageTitle = 'Ubah Password Akun';
        // Kita akan buat view ini nanti: resources/views/customer_area/account/change-password.blade.php
        return view('customer_area.account.change-password', compact('pageTitle'));
    }

    /**
     * Memproses permintaan untuk mengubah password pelanggan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        /** @var Customer $customer */// Type hint untuk Intelephense
        $customer = Auth::guard('customer_web')->user();

        if (! $customer) { // Pengaman tambahan, meskipun middleware sudah ada
            return redirect()->route('customer.login.form')->with('error', 'Sesi Anda tidak valid. Silakan login kembali.');
        }

        // Validasi input
        $request->validate([
            'current_password' => [
                'required',
                // Fungsi kustom untuk mengecek apakah password saat ini benar
                function ($attribute, $value, $fail) use ($customer) {
                    if (! Hash::check($value, $customer->password)) {
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
                                  // ->symbols(),     // Harus ada simbol (opsional)
                'confirmed',      // Harus cocok dengan field 'new_password_confirmation'
            ],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required'     => 'Password baru wajib diisi.',
            'new_password.min'          => 'Password baru minimal :min karakter.',
            // 'new_password.mixed_case'   => 'Password baru harus mengandung huruf besar dan huruf kecil.',
            // 'new_password.numbers'      => 'Password baru harus mengandung angka.',
            // 'new_password.symbols'      => 'Password baru harus mengandung simbol.',
            'new_password.confirmed'    => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Jika validasi lolos, update password pelanggan
        // Model Customer sudah punya cast 'password' => 'hashed'
        $customer->password = $request->new_password;
        $customer->save();

                                                                          // Beri pesan sukses dan redirect kembali ke form ubah password
        return redirect()->route('customer.account.change_password.form') // Kita akan buat nama rute ini
            ->with('success', 'Password Anda berhasil diubah!');
    }
}
