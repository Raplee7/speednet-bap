<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Device_sn;
use App\Models\Payment; // Asumsi kamu punya model DeviceSn (dari rute device_sns)
use Carbon\Carbon;      // Asumsi kamu punya model Paket
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

// Untuk query raw jika diperlukan (misalnya untuk grouping chart)

class DashboardController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $pageTitle = 'Dashboard';
        $today     = Carbon::today();

        // --- Data untuk Kartu Statistik Utama ---
        $totalCustomers       = Customer::count();
        $totalActiveCustomers = Customer::where('status', 'terpasang')->count(); // Status 'terpasang' dari kodemu

        // Total Pendapatan Bulan Ini (Baru)
        $incomeThisMonth = Payment::where('status_pembayaran', 'paid')
            ->whereYear('tanggal_pembayaran', $today->year)
            ->whereMonth('tanggal_pembayaran', $today->month)
            ->sum('jumlah_tagihan'); // Asumsi kolom 'jumlah_tagihan' di tabel payments

                                                                                                                    // Total Tagihan Belum Dibayar (Baru)
        $totalUnpaidInvoicesAmount = Payment::whereIn('status_pembayaran', ['belum_lunas', 'pending_confirmation']) // Atau status lain yang relevan
            ->sum('jumlah_tagihan');
        $countUnpaidInvoices = Payment::whereIn('status_pembayaran', ['belum_lunas', 'pending_confirmation'])
            ->count();

                                            // Total Perangkat (Baru) - Asumsi dari model DeviceSn
        $totalDevices = Device_sn::count(); // Sesuaikan jika nama model/tabelnya berbeda

        // --- Data untuk Aksi Cepat (Tabel) ---
        // Pelanggan Baru (Butuh Konfirmasi) - dari kodemu
        $newCustomersNeedingConfirmationList = Customer::where('status', 'baru')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        $countNewCustomersNeedingConfirmation = Customer::where('status', 'baru')->count();

        // Pelanggan Akan Segera Habis Masa Aktif - dari kodemu (dengan sedikit penyesuaian Carbon)
        $daysThreshold            = 5;
        $endDateThreshold         = $today->copy()->addDays($daysThreshold);
        $activeCustomersForExpiry = Customer::where('status', 'terpasang')->get(); // Hapus with('paket') jika tidak dipakai di loop
        $expiringSoonCustomers    = [];

        foreach ($activeCustomersForExpiry as $customer) {
            $latestPaidPayment = Payment::where('customer_id', $customer->id_customer)
                ->where('status_pembayaran', 'paid')
                ->orderBy('periode_tagihan_selesai', 'desc')
                ->first();
            if ($latestPaidPayment && $latestPaidPayment->periode_tagihan_selesai) { // Pastikan periode_tagihan_selesai tidak null
                $periodeSelesai = Carbon::parse($latestPaidPayment->periode_tagihan_selesai);
                if ($periodeSelesai->isFuture() && $periodeSelesai->lte($endDateThreshold)) {
                    $customer->layanan_berakhir_pada = $periodeSelesai;
                    // Penyesuaian sisa_hari agar benar-benar sisa hari, bukan jumlah hari dalam rentang
                    $customer->sisa_hari     = max(0, $today->diffInDays($periodeSelesai, false));
                    $expiringSoonCustomers[] = $customer;
                }
            }
        }
        $expiringSoonCustomers = collect($expiringSoonCustomers)->sortBy('sisa_hari')->values()->all(); // Sort by sisa_hari

        // Pembayaran Terbaru Menunggu Konfirmasi - dari kodemu
        $latestPendingPayments = Payment::where('status_pembayaran', 'pending_confirmation')
            ->with('customer') // Eager load customer
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
        $countPendingConfirmationPayments = Payment::where('status_pembayaran', 'pending_confirmation')->count(); // Untuk link "Lihat Semua"

        // --- Data untuk Chart ---
        // Bar Chart Pendapatan 6 Bulan Terakhir - dari kodemu
        $monthlyIncomeData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month  = Carbon::now()->subMonths($i);
            $income = Payment::where('status_pembayaran', 'paid')
                ->whereYear('tanggal_pembayaran', $month->year)
                ->whereMonth('tanggal_pembayaran', $month->month)
                ->sum('jumlah_tagihan');
            $monthlyIncomeData[] = [
                'month'  => $month->locale('id')->translatedFormat('M Y'),
                'income' => $income,
            ];
        }

                                                                           // Donut Chart Pemakai paket internet terbanyak (Baru)
                                                                           // Asumsi: Customer punya relasi ke Paket (misalnya customer->paket->nama_paket atau join)
                                                                           // Atau, jika paket ada di tabel customer (misal kolom paket_id lalu join ke tabel pakets)
                                                                           // Saya asumsikan ada kolom 'paket_id' di tabel 'customers' yang berelasi ke 'id_paket' di tabel 'pakets'
        $paketUsageData = Customer::where('customers.status', 'terpasang') // Hanya pelanggan aktif
            ->join('pakets', 'customers.paket_id', '=', 'pakets.id_paket')     // Sesuaikan nama kolom dan tabel jika beda
            ->select('pakets.kecepatan_paket', DB::raw('count(customers.id_customer) as total_pengguna'))
            ->groupBy('pakets.kecepatan_paket')
            ->orderBy('total_pengguna', 'desc')
            ->get();

        return view('dashboard', [ // Pastikan nama viewnya benar (dashboard.blade.php atau dashboard/index.blade.php)
            'pageTitle'                            => $pageTitle,
            // Kartu Angka
            'totalCustomers'                       => $totalCustomers,
            'totalActiveCustomers'                 => $totalActiveCustomers,
            'incomeThisMonth'                      => $incomeThisMonth,           // Baru
            'totalUnpaidInvoicesAmount'            => $totalUnpaidInvoicesAmount, // Baru
            'countUnpaidInvoices'                  => $countUnpaidInvoices,       // Baru
            'totalDevices'                         => $totalDevices,              // Baru
                                                                                  // Aksi Cepat
            'expiringSoonCustomers'                => $expiringSoonCustomers,
            'daysThreshold'                        => $daysThreshold,
            'latestPendingPayments'                => $latestPendingPayments,
            'countPendingConfirmationPayments'     => $countPendingConfirmationPayments, // Untuk link 'Lihat semua'
            'newCustomersNeedingConfirmationList'  => $newCustomersNeedingConfirmationList,
            'countNewCustomersNeedingConfirmation' => $countNewCustomersNeedingConfirmation,
            // Charts
            'monthlyIncomeData'                    => $monthlyIncomeData,
            'paketUsageData'                       => $paketUsageData, // Baru
        ]);
    }
}
