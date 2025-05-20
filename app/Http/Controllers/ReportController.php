<?php
namespace App\Http\Controllers;

use App\Exports\CustomerPaymentReportExport;
use App\Exports\FinancialReportExport;
use App\Models\Customer;
use App\Models\Paket;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Method private untuk mengambil dan memproses data laporan pembayaran pelanggan.
     */
    private function getCustomerPaymentReportData(Request $request, $forExport = false) // Nama method diubah
    {
        $currentCarbon       = Carbon::now();
        $requestedYear       = $request->input('year');
        $requestedStartMonth = $request->input('start_month');
        $requestedEndMonth   = $request->input('end_month');

        $selectedYear       = $requestedYear ? (int) $requestedYear : null;
        $selectedStartMonth = $requestedStartMonth ? (int) $requestedStartMonth : null;
        $selectedEndMonth   = $requestedEndMonth ? (int) $requestedEndMonth : null;

        Log::info("Laporan Pembayaran Pelanggan: Year={$selectedYear}, StartM={$selectedStartMonth}, EndM={$selectedEndMonth}");

        $availableYears = [];
        for ($year = $currentCarbon->year + 1; $year >= $currentCarbon->year - 4; $year--) {
            $availableYears[$year] = $year;
        }

        $allMonthNames = [];
        for ($m = 1; $m <= 12; $m++) {
            $allMonthNames[$m] = Carbon::create()->month($m)->locale('id')->translatedFormat('F');
        }

        $reportDataProcessed            = collect();
        $customersCollectionOrPaginator = collect();
        $displayedMonths                = [];

        if ($selectedYear && $selectedStartMonth && $selectedEndMonth) {
            if ($selectedStartMonth > $selectedEndMonth) {
                $temp               = $selectedStartMonth;
                $selectedStartMonth = $selectedEndMonth;
                $selectedEndMonth   = $temp;
            }
            for ($m = $selectedStartMonth; $m <= $selectedEndMonth; $m++) {
                if (isset($allMonthNames[$m])) {$displayedMonths[$m] = $allMonthNames[$m];}
            }

            $targetPeriodStart = Carbon::create($selectedYear, $selectedStartMonth, 1)->startOfMonth();
            $targetPeriodEnd   = Carbon::create($selectedYear, $selectedEndMonth, 1)->endOfMonth();

            $customersQuery = Customer::with(['paket', 'payments' => function ($query) use ($selectedYear, $targetPeriodStart, $targetPeriodEnd) {
                $query->where(function ($q) use ($targetPeriodStart, $targetPeriodEnd) {
                    $q->where('periode_tagihan_mulai', '<=', $targetPeriodEnd)
                        ->where('periode_tagihan_selesai', '>=', $targetPeriodStart);
                })->orderBy('periode_tagihan_mulai', 'asc');
            }])->orderBy('nama_customer', 'asc');

            $customersData                  = $forExport ? $customersQuery->get() : $customersQuery->paginate(10)->withQueryString();
            $customersCollectionOrPaginator = $customersData; // Tetap gunakan nama ini untuk konsistensi dengan return

            foreach ($customersData as $customer) {
                $monthlyStatus = [];
                foreach ($displayedMonths as $monthNumber => $monthName) {
                    $currentIterationMonthStart = Carbon::create($selectedYear, $monthNumber, 1)->startOfMonth();
                    $currentIterationMonthEnd   = Carbon::create($selectedYear, $monthNumber, 1)->endOfMonth();
                    $statusForThisMonth         = ['text' => '-', 'class' => 'text-muted', 'tgl_bayar' => null, 'invoice_no' => null, 'payment_id' => null];
                    foreach ($customer->payments as $payment) {
                        $periodeMulai   = Carbon::parse($payment->periode_tagihan_mulai);
                        $periodeSelesai = Carbon::parse($payment->periode_tagihan_selesai);
                        if (! ($periodeSelesai->lt($currentIterationMonthStart) || $periodeMulai->gt($currentIterationMonthEnd))) {
                            $statusText  = Str::title(str_replace('_', ' ', $payment->status_pembayaran));
                            $statusClass = 'text-muted';
                            if ($payment->status_pembayaran == 'paid') {$statusClass = 'status-paid';
                                $statusText                             = 'Lunas';} elseif ($payment->status_pembayaran == 'unpaid') {
                                if (Carbon::parse($payment->tanggal_jatuh_tempo)->isPast() && $currentIterationMonthStart->gte($periodeMulai) && ! $periodeSelesai->isPast()) {
                                    $statusClass = 'status-menunggak';
                                    $statusText  = 'Menunggak';
                                } else { $statusClass = 'status-unpaid';
                                    $statusText                              = 'Belum Bayar';}
                            } elseif ($payment->status_pembayaran == 'pending_confirmation') {$statusClass = 'status-pending_confirmation';
                                $statusText                             = 'Pending';} elseif ($payment->status_pembayaran == 'failed') {$statusClass = 'status-failed';} elseif ($payment->status_pembayaran == 'cancelled') {$statusClass = 'status-cancelled';}
                            $statusForThisMonth = [
                                'text'       => $statusText, 'class'                  => $statusClass,
                                'tgl_bayar'  => $payment->status_pembayaran == 'paid' && $payment->tanggal_pembayaran ? Carbon::parse($payment->tanggal_pembayaran)->format('d/m/Y') : null,
                                'invoice_no' => $payment->nomor_invoice, 'payment_id' => $payment->id_payment,
                            ];
                            break;
                        }
                    }
                    $monthlyStatus[$monthName] = $statusForThisMonth;
                }
                $latestOverallPaidPayment = $customer->payments()->where('status_pembayaran', 'paid')->orderBy('periode_tagihan_selesai', 'desc')->first();
                $tglLayananHabisTerakhir  = $latestOverallPaidPayment ? Carbon::parse($latestOverallPaidPayment->periode_tagihan_selesai) : null;
                $reportDataProcessed->push([
                    'customer'                          => $customer,
                    'paket_info'                        => $customer->paket ? ($customer->paket->kecepatan_paket . ' (Rp ' . number_format($customer->paket->harga_paket, 0, ',', '.') . ')') : '-',
                    'tgl_aktivasi'                      => $customer->tanggal_aktivasi ? Carbon::parse($customer->tanggal_aktivasi)->locale('id')->translatedFormat('d M Y') : '-',
                    'tgl_layanan_habis_terakhir_visual' => $tglLayananHabisTerakhir ? $tglLayananHabisTerakhir->copy()->addDay()->locale('id')->translatedFormat('d M Y') : 'N/A',
                    'tgl_layanan_habis_sebenarnya'      => $tglLayananHabisTerakhir ? $tglLayananHabisTerakhir->locale('id')->translatedFormat('d M Y') : null,
                    'monthly_status'                    => $monthlyStatus,
                ]);
            }
        }
        return [
            'reportData'         => $reportDataProcessed,
            'customersPaginator' => $customersCollectionOrPaginator,
            'availableYears'     => $availableYears,
            'selectedYear'       => $selectedYear,
            'allMonthNames'      => $allMonthNames,
            'selectedStartMonth' => $selectedStartMonth,
            'selectedEndMonth'   => $selectedEndMonth,
            'displayedMonths'    => $displayedMonths,
        ];
    }

    /**
     * Menampilkan Laporan Pembayaran Pelanggan (HTML).
     */
    public function customerPaymentReport(Request $request) // Nama method diubah
    {
        $pageTitle = 'Laporan Pembayaran Pelanggan';                                                     // Judul diubah
        $data      = $this->getCustomerPaymentReportData($request, false);                               // Memanggil method yang sudah direfaktor
        return view('reports.customer_payment_report', array_merge(['pageTitle' => $pageTitle], $data)); // View diubah
    }

    /**
     * Export Laporan Pembayaran Pelanggan ke PDF.
     */
    public function exportCustomerPaymentReportPdf(Request $request) // Nama method diubah
    {
        $pageTitle = 'Laporan Pembayaran Pelanggan'; // Judul diubah
        $data      = $this->getCustomerPaymentReportData($request, true);

        if ($data['reportData']->isEmpty() && (! $data['selectedYear'] || ! $data['selectedStartMonth'] || ! $data['selectedEndMonth'])) {
            return redirect()->route('reports.customer_payment', $request->query())->with('error', 'Silakan pilih filter tahun dan rentang bulan terlebih dahulu untuk export PDF.'); // Rute diubah
        }

        // View untuk PDF juga perlu diubah namanya jika file view-nya diubah
        $pdf = PDF::loadView('reports.customer_payment_report_pdf', array_merge(['pageTitle' => $pageTitle], $data))
            ->setPaper('a4', 'landscape');

        $fileName = 'laporan_pembayaran_pelanggan_' . $data['selectedYear']; // Nama file diubah
        if ($data['selectedStartMonth'] && isset($data['allMonthNames'][$data['selectedStartMonth']])) {
            $fileName .= '_' . Str::slug($data['allMonthNames'][$data['selectedStartMonth']]);
        }
        if ($data['selectedEndMonth'] && $data['selectedEndMonth'] != $data['selectedStartMonth'] && isset($data['allMonthNames'][$data['selectedEndMonth']])) {
            $fileName .= '-sd-' . Str::slug($data['allMonthNames'][$data['selectedEndMonth']]);
        }
        $fileName .= '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Export Laporan Pembayaran Pelanggan ke Excel.
     */
    public function exportCustomerPaymentReportExcel(Request $request) // Nama method diubah
    {
        $data = $this->getCustomerPaymentReportData($request, true);

        if ($data['reportData']->isEmpty() && (! $data['selectedYear'] || ! $data['selectedStartMonth'] || ! $data['selectedEndMonth'])) {
            return redirect()->route('reports.customer_payment', $request->query())->with('error', 'Silakan pilih filter tahun dan rentang bulan terlebih dahulu untuk export Excel.'); // Rute diubah
        }

        $fileName = 'laporan_pembayaran_pelanggan_' . $data['selectedYear']; // Nama file diubah
        if ($data['selectedStartMonth'] && isset($data['allMonthNames'][$data['selectedStartMonth']])) {
            $fileName .= '_' . Str::slug($data['allMonthNames'][$data['selectedStartMonth']]);
        }
        if ($data['selectedEndMonth'] && $data['selectedEndMonth'] != $data['selectedStartMonth'] && isset($data['allMonthNames'][$data['selectedEndMonth']])) {
            $fileName .= '-sd-' . Str::slug($data['allMonthNames'][$data['selectedEndMonth']]);
        }
        $fileName .= '.xlsx';

        // Pastikan nama export class sesuai jika Anda mengubahnya juga
        return Excel::download(new CustomerPaymentReportExport($data), $fileName);
    }

    private function getFinancialReportData(Request $request)
    {
        $pageTitle         = 'Laporan Keuangan Pendapatan';
        $periodType        = $request->input('period_type', 'monthly');
        $selectedDate      = $request->input('selected_date', Carbon::now()->toDateString());
        $selectedMonthYear = $request->input('selected_month_year', Carbon::now()->format('Y-m'));
        $selectedYearOnly  = $request->input('selected_year_only', Carbon::now()->year);
        $customStartDate   = $request->input('custom_start_date');
        $customEndDate     = $request->input('custom_end_date');

        $startDate         = Carbon::now()->startOfDay();
        $endDate           = Carbon::now()->endOfDay();
        $reportPeriodLabel = '';

        // ... (switch case untuk $periodType, $startDate, $endDate, $reportPeriodLabel tetap sama) ...
        switch ($periodType) {
            case 'daily':
                $startDate         = Carbon::parse($selectedDate)->startOfDay();
                $endDate           = Carbon::parse($selectedDate)->endOfDay();
                $reportPeriodLabel = 'Harian: ' . $startDate->locale('id')->translatedFormat('d F Y');
                break;
            case 'weekly':
                $endDate           = Carbon::parse($selectedDate)->endOfDay();
                $startDate         = $endDate->copy()->subDays(6)->startOfDay();
                $reportPeriodLabel = 'Mingguan: ' . $startDate->locale('id')->translatedFormat('d M Y') . ' - ' . $endDate->locale('id')->translatedFormat('d M Y');
                break;
            case 'monthly':
                try { $parsedMonthYear = Carbon::createFromFormat('Y-m', $selectedMonthYear)->startOfMonth();} catch (\Exception $e) {$parsedMonthYear = Carbon::now()->startOfMonth();
                    $selectedMonthYear                         = $parsedMonthYear->format('Y-m');}
                $startDate         = $parsedMonthYear->copy()->startOfMonth();
                $endDate           = $parsedMonthYear->copy()->endOfMonth();
                $reportPeriodLabel = 'Bulanan: ' . $startDate->locale('id')->translatedFormat('F Y');
                break;
            case 'yearly':
                try { $parsedYear = (int) $selectedYearOnly;if ($parsedYear < 2000 || $parsedYear > Carbon::now()->year + 5) {throw new \Exception("Tahun tidak valid");}} catch (\Exception $e) {$parsedYear = Carbon::now()->year;
                    $selectedYearOnly                     = $parsedYear;}
                $startDate         = Carbon::createFromDate($parsedYear, 1, 1)->startOfYear();
                $endDate           = Carbon::createFromDate($parsedYear, 12, 31)->endOfYear();
                $reportPeriodLabel = 'Tahunan: ' . $parsedYear;
                break;
            case 'custom':
                if ($customStartDate && $customEndDate) {
                    try {
                        $startDate = Carbon::parse($customStartDate)->startOfDay();
                        $endDate   = Carbon::parse($customEndDate)->endOfDay();
                        if ($startDate->gt($endDate)) {$periodType = 'monthly';
                            $selectedMonthYear                    = Carbon::now()->format('Y-m');
                            $startDate                            = Carbon::now()->startOfMonth();
                            $endDate                              = Carbon::now()->endOfMonth();
                            $reportPeriodLabel                    = 'Bulanan: ' . $startDate->locale('id')->translatedFormat('F Y');} else { $reportPeriodLabel = 'Kustom: ' . $startDate->locale('id')->translatedFormat('d M Y') . ' - ' . $endDate->locale('id')->translatedFormat('d M Y');}
                    } catch (\Exception $e) {$periodType = 'monthly';
                        $selectedMonthYear                    = Carbon::now()->format('Y-m');
                        $startDate                            = Carbon::now()->startOfMonth();
                        $endDate                              = Carbon::now()->endOfMonth();
                        $reportPeriodLabel                    = 'Bulanan: ' . $startDate->locale('id')->translatedFormat('F Y');}
                } else { $periodType = 'monthly';
                    $selectedMonthYear                     = Carbon::now()->format('Y-m');
                    $startDate                             = Carbon::now()->startOfMonth();
                    $endDate                               = Carbon::now()->endOfMonth();
                    $reportPeriodLabel                     = 'Bulanan: ' . $startDate->locale('id')->translatedFormat('F Y');}
                break;
            default:
                $periodType        = 'monthly';
                $selectedMonthYear = Carbon::now()->format('Y-m');
                $startDate         = Carbon::now()->startOfMonth();
                $endDate           = Carbon::now()->endOfMonth();
                $reportPeriodLabel = 'Bulanan: ' . $startDate->locale('id')->translatedFormat('F Y');
        }

        $paymentsQuery = Payment::where('status_pembayaran', 'paid')->whereBetween('tanggal_pembayaran', [$startDate->toDateString(), $endDate->toDateString()]);
        $totalIncome   = (clone $paymentsQuery)->sum('jumlah_tagihan');
        $incomeByPaket = (clone $paymentsQuery)->join('pakets', 'payments.paket_id', '=', 'pakets.id_paket')
            ->select('pakets.kecepatan_paket', DB::raw('SUM(payments.jumlah_tagihan) as total'), DB::raw('COUNT(payments.id_payment) as transaction_count'))
            ->groupBy('pakets.id_paket', 'pakets.kecepatan_paket')->orderBy('pakets.harga_paket')->get();

        // PERUBAHAN LOGIKA UNTUK $incomeByMethod
        $incomeByMethodData = (clone $paymentsQuery)
            ->leftJoin('ewallets', 'payments.ewallet_id', '=', 'ewallets.id_ewallet') // Left join agar metode non-transfer tetap masuk
            ->select(
                'payments.metode_pembayaran',
                'ewallets.nama_ewallet',
                DB::raw('SUM(payments.jumlah_tagihan) as total'),
                DB::raw('COUNT(payments.id_payment) as transaction_count')
            )
            ->groupBy('payments.metode_pembayaran', 'ewallets.nama_ewallet') // Grup juga dengan nama ewallet
            ->orderBy('payments.metode_pembayaran')
            ->get();

        $incomeByMethod = collect();
        foreach ($incomeByMethodData as $item) {
            $methodName = Str::title($item->metode_pembayaran ?? 'Tidak Diketahui');
            if ($item->metode_pembayaran == 'transfer' && $item->nama_ewallet) {
                $methodName = 'Transfer via ' . Str::title($item->nama_ewallet);
            }
            // Jika sudah ada key dengan nama metode yang sama (misal beberapa 'Transfer via X'), kita jumlahkan
            if ($incomeByMethod->has($methodName)) {
                $existing                    = $incomeByMethod[$methodName];
                $incomeByMethod[$methodName] = [
                    'total' => $existing['total'] + $item->total,
                    'count' => $existing['count'] + $item->transaction_count,
                ];
            } else {
                $incomeByMethod[$methodName] = [
                    'total' => $item->total,
                    'count' => $item->transaction_count,
                ];
            }
        }
        // AKHIR PERUBAHAN LOGIKA UNTUK $incomeByMethod

        $previousPeriodIncome = null;
        // ... (logika previousPeriodIncome tetap sama) ...
        if ($periodType !== 'custom') {
            $prevStartDate = null;
            $prevEndDate   = null;
            switch ($periodType) {
                case 'daily':$prevStartDate = $startDate->copy()->subDay();
                    $prevEndDate                = $endDate->copy()->subDay();
                    break;
                case 'weekly':$prevStartDate = $startDate->copy()->subWeek();
                    $prevEndDate                 = $endDate->copy()->subWeek();
                    break;
                case 'monthly':$prevStartDate = $startDate->copy()->subMonthNoOverflow();
                    $prevEndDate                  = $prevStartDate->copy()->endOfMonth();
                    break;
                case 'yearly':$prevStartDate = $startDate->copy()->subYearNoOverflow();
                    $prevEndDate                 = $prevStartDate->copy()->endOfYear();
                    break;
            }
            if ($prevStartDate && $prevEndDate) {
                $previousPeriodIncome = Payment::where('status_pembayaran', 'paid')->whereBetween('tanggal_pembayaran', [$prevStartDate, $prevEndDate])->sum('jumlah_tagihan');
            }
        }

        return compact(
            'pageTitle', 'periodType', 'selectedDate', 'selectedMonthYear', 'selectedYearOnly',
            'customStartDate', 'customEndDate', 'reportPeriodLabel',
            'totalIncome', 'incomeByPaket', 'incomeByMethod', 'previousPeriodIncome'
        );
    }

    /**
     * Menampilkan Laporan Keuangan Pendapatan (HTML).
     */
    public function financialReport(Request $request)
    {
        $pageTitle = 'Laporan Keuangan Pendapatan';
        $data      = $this->getFinancialReportData($request); // Panggil method private
        return view('reports.financial_report', array_merge(['pageTitle' => $pageTitle], $data));
    }

    /**
     * Export Laporan Keuangan Pendapatan ke PDF.
     */
    public function exportFinancialReportPdf(Request $request)
    {
        $pageTitle = 'Laporan Keuangan Pendapatan';
        $data      = $this->getFinancialReportData($request);

        if (empty($data['reportPeriodLabel'])) { // Cek jika filter belum valid/diterapkan
            return redirect()->route('reports.financial', $request->query())->with('error', 'Silakan pilih filter periode yang valid terlebih dahulu untuk export PDF.');
        }

        $pdf = PDF::loadView('reports.financial_report_pdf', array_merge(['pageTitle' => $pageTitle], $data))
            ->setPaper('a4', 'portrait'); // Portrait untuk laporan keuangan biasanya cukup

        $fileName = 'laporan_keuangan_pendapatan_' . Str::slug($data['reportPeriodLabel'], '_') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Export Laporan Keuangan Pendapatan ke Excel.
     */
    public function exportFinancialReportExcel(Request $request)
    {
        $data = $this->getFinancialReportData($request);

        if (empty($data['reportPeriodLabel'])) {
            return redirect()->route('reports.financial', $request->query())->with('error', 'Silakan pilih filter periode yang valid terlebih dahulu untuk export Excel.');
        }

        $fileName = 'laporan_keuangan_pendapatan_' . Str::slug($data['reportPeriodLabel'], '_') . '.xlsx';

        return Excel::download(new FinancialReportExport($data), $fileName);
        return redirect()->route('reports.financial', $request->query())->with('info', 'Fitur Export Excel untuk Laporan Keuangan sedang dalam pengembangan.');
    }
}
