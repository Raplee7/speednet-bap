<?php
namespace App\Http\Controllers; // Pastikan namespace ini sesuai dengan lokasi file Anda

use App\Http\Controllers\Controller; // PENTING: Pastikan use statement ini ada dan benar
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthCustomerController extends BaseController// PENTING: Pastikan extends Controller

{
    public function __construct()
    {
        // Metode middleware() ini ada di base Controller Laravel
        // Hanya guest (pelanggan yang belum login) yang bisa akses method login
        // Method logout hanya untuk pelanggan yang sudah login
        $this->middleware('guest:customer_web')->except('logout');
    }

    /**
     * Menampilkan form login pelanggan.
     * Jika login hanya via modal, ini akan redirect ke landing page dan memicu modal.
     */
    public function showLoginForm()
    {
        return redirect('/')->with('open_customer_login_modal', true);
    }

    /**
     * Menangani percobaan login pelanggan dari modal.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'active_user_modal' => 'required|string', // Sesuaikan nama field jika berbeda di modal Anda
            'password_modal'    => 'required|string',
        ], [
            'active_user_modal.required' => 'Username pelanggan tidak boleh kosong.',
            'password_modal.required'    => 'Password tidak boleh kosong.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'customer_login') // Error bag khusus untuk login pelanggan
                ->withInput($request->except('password_modal'))
                ->with('open_customer_login_modal', true); // Flag untuk buka modal lagi di landing page
        }

        // Mencoba login menggunakan guard 'customer_web'
        // dan menggunakan 'active_user' sebagai username field (sesuai method username() di model Customer)
        if (Auth::guard('customer_web')->attempt([
            'active_user' => $request->active_user_modal, // Kolom di DB adalah 'active_user'
            'password'    => $request->password_modal,
        ], $request->filled('remember_modal'))) { // Menambahkan fitur "remember me"
            $request->session()->regenerate();

                                                                      // Arahkan ke dashboard pelanggan
            return redirect()->intended(route('customer.dashboard')); // Pastikan rute 'customer.dashboard' ada
        }

        // Jika login gagal
        return redirect()->back()
            ->withErrors(['active_user_modal' => 'Login gagal! Username atau password salah.'], 'customer_login')
            ->withInput($request->except('password_modal'))
            ->with('open_customer_login_modal', true);
    }

    /**
     * Logout pelanggan.
     */
    public function logout(Request $request)
    {
        Auth::guard('customer_web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/'); // Arahkan ke landing page setelah logout
    }
}
