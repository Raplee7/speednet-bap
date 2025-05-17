<?php
namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard untuk pelanggan yang sudah login.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $customer = Auth::guard('customer_web')->user();

        if (! $customer) {
            return redirect()->route('customer.login.form')->with('error', 'Sesi Anda tidak valid. Silakan login kembali.');
        }

        $pageTitle = 'Dashboard Pelanggan';

        // Ambil tagihan terbaru yang statusnya 'unpaid' untuk pelanggan ini
        $mostRecentUnpaidInvoice = Payment::where('customer_id', $customer->id_customer)
            ->where('status_pembayaran', 'unpaid')
            ->orderBy('created_at', 'desc') // Ambil yang paling baru dibuat
            ->first();

        // Hitung jumlah semua tagihan yang belum dibayar
        $countUnpaidInvoices = Payment::where('customer_id', $customer->id_customer)
            ->where('status_pembayaran', 'unpaid')
            ->count();

        return view('customer_area.dashboard', compact(
            'customer',
            'pageTitle',
            'mostRecentUnpaidInvoice', // Kirim data tagihan unpaid terbaru
            'countUnpaidInvoices'      // Kirim jumlah tagihan unpaid
        ));
    }

}
