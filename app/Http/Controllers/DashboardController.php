<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {

        $pageTitle = 'Dashboard';

        // --- Data untuk Kartu Statistik Utama ---
        $totalCustomers       = Customer::count();
        $totalActiveCustomers = Customer::where('status', 'terpasang')->count();

        $pendingConfirmationPayments = Payment::where('status_pembayaran', 'pending_confirmation')->count();

        $newCustomersNeedingConfirmationList = Customer::where('status', 'baru')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        $countNewCustomersNeedingConfirmation = Customer::where('status', 'baru')->count();

        // --- Data untuk Pelanggan yang Akan Segera Habis Masa Aktif ---
        $daysThreshold    = 5;
        $today            = Carbon::today();
        $endDateThreshold = $today->copy()->addDays($daysThreshold);

        $activeCustomersForExpiry = Customer::where('status', 'terpasang')->with('paket')->get();
        $expiringSoonCustomers    = [];

        foreach ($activeCustomersForExpiry as $customer) {

            $latestPaidPayment = Payment::where('customer_id', $customer->id_customer)
                ->where('status_pembayaran', 'paid')
                ->orderBy('periode_tagihan_selesai', 'desc')
                ->first();
            if ($latestPaidPayment) {
                $periodeSelesai = Carbon::parse($latestPaidPayment->periode_tagihan_selesai);
                if ($periodeSelesai->isFuture() && $periodeSelesai->lte($endDateThreshold)) {
                    $customer->layanan_berakhir_pada = $periodeSelesai;
                    $customer->sisa_hari             = max(0, $today->diffInDays($periodeSelesai, false) + 1);
                    $expiringSoonCustomers[]         = $customer;
                }
            }

        }
        $expiringSoonCustomers = collect($expiringSoonCustomers)->sortBy('layanan_berakhir_pada')->values()->all();

        // --- Data untuk Tagihan Terbaru Menunggu Konfirmasi ---
        $latestPendingPayments = Payment::where('status_pembayaran', 'pending_confirmation')
            ->with('customer')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // --- Data untuk Grafik Pendapatan 6 Bulan Terakhir ---
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

        return view('dashboard', [
            'pageTitle'                            => $pageTitle,
            'totalCustomers'                       => $totalCustomers,
            'totalActiveCustomers'                 => $totalActiveCustomers,
            'pendingConfirmationPayments'          => $pendingConfirmationPayments,
            // 'incomeThisMonth'                 => $incomeThisMonth,
            'expiringSoonCustomers'                => $expiringSoonCustomers,
            'daysThreshold'                        => $daysThreshold,
            'latestPendingPayments'                => $latestPendingPayments,
            'monthlyIncomeData'                    => $monthlyIncomeData,
            'newCustomersNeedingConfirmationList'  => $newCustomersNeedingConfirmationList,
            'countNewCustomersNeedingConfirmation' => $countNewCustomersNeedingConfirmation,

        ]);
    }
}
