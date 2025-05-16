<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

// Penting untuk mendapatkan data pelanggan yang login
// Anda mungkin perlu use App\Models\Customer; jika melakukan type-hinting atau query tambahan

class CustomerDashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard untuk pelanggan yang sudah login.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Dapatkan data pelanggan yang sedang login menggunakan guard 'customer_web'
        $customer = Auth::guard('customer_web')->user();

        // Jika karena suatu alasan pelanggan tidak ditemukan (seharusnya tidak terjadi jika middleware auth:customer_web bekerja)
        if (! $customer) {
            // Redirect ke halaman login pelanggan dengan pesan error
            return redirect()->route('customer.login.form')->with('error', 'Sesi Anda tidak valid. Silakan login kembali.');
        }

        // Data untuk dikirim ke view
        $pageTitle = 'Dashboard Pelanggan';

        // Kirim data pelanggan dan pageTitle ke view
        // Kita akan membuat view 'customer.dashboard' nanti
        return view('customer_area.dashboard', compact('customer', 'pageTitle'));
    }

    // Anda bisa menambahkan method lain di sini untuk fitur pelanggan lainnya
    // Misalnya: showTagihan(), showProfil(), updateProfil(), dll.
}
