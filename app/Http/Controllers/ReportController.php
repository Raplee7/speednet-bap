<?php
namespace App\Http\Controllers;

use App\Exports\AllInvoicesReportExport;
use App\Exports\CustomerPaymentReportExport;
use App\Exports\CustomerProfileReportExport;
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
     * Helper method untuk menentukan rentang tanggal berdasarkan input periode.
     * Mengembalikan array ['startDate', 'endDate', 'reportPeriodLabel', 'periodType',
     * 'selectedDate', 'selectedMonthYear', 'selectedYearOnly', 'customStartDate', 'customEndDate']
     */
    private function determinePeriodRange(Request $request, string $dateColumnPrefix = ''): array
    {
        $periodType    = $request->input($dateColumnPrefix . 'period_type', 'all');
        $currentCarbon = Carbon::now();

        $selectedDate      = $request->input($dateColumnPrefix . 'selected_date', $currentCarbon->toDateString());
        $selectedMonthYear = $request->input($dateColumnPrefix . 'selected_month_year', $currentCarbon->format('Y-m'));
        $selectedYearOnly  = $request->input($dateColumnPrefix . 'selected_year_only', $currentCarbon->year);

        $customStartDate = $request->input($dateColumnPrefix . 'custom_start_date');
        $customEndDate   = $request->input($dateColumnPrefix . 'custom_end_date');

        $startDate         = null;
        $endDate           = null;
        $reportPeriodLabel = 'Semua Periode';

        try {
            if ($periodType === 'all') {
                // Biarkan startDate dan endDate null
            } elseif ($periodType === 'daily') {
                $startDate         = Carbon::parse($selectedDate)->startOfDay();
                $endDate           = Carbon::parse($selectedDate)->endOfDay();
                $reportPeriodLabel = 'Harian: ' . $startDate->locale('id')->translatedFormat('d F Y');
            } elseif ($periodType === 'weekly') {
                $endDate           = Carbon::parse($selectedDate)->endOfDay();
                $startDate         = $endDate->copy()->subDays(6)->startOfDay();
                $reportPeriodLabel = 'Mingguan: ' . $startDate->locale('id')->translatedFormat('d M Y') . ' s.d. ' . $endDate->locale('id')->translatedFormat('d M Y');
            } elseif ($periodType === 'monthly') {
                $parsedMonthYear   = Carbon::createFromFormat('Y-m', $selectedMonthYear)->startOfMonth();
                $startDate         = $parsedMonthYear->copy()->startOfMonth();
                $endDate           = $parsedMonthYear->copy()->endOfMonth();
                $reportPeriodLabel = 'Bulanan: ' . $startDate->locale('id')->translatedFormat('F Y');
            } elseif ($periodType === 'yearly') {
                $parsedYear = (int) $selectedYearOnly;
                if ($parsedYear < 2000 || $parsedYear > $currentCarbon->year + 5) {throw new \Exception("Tahun tidak valid");}
                $startDate         = Carbon::createFromDate($parsedYear, 1, 1)->startOfYear();
                $endDate           = Carbon::createFromDate($parsedYear, 12, 31)->endOfYear();
                $reportPeriodLabel = 'Tahunan: ' . $parsedYear;
            } elseif ($periodType === 'custom') {
                if ($customStartDate && $customEndDate) {
                    $startDate = Carbon::parse($customStartDate)->startOfDay();
                    $endDate   = Carbon::parse($customEndDate)->endOfDay();
                    if ($startDate->gt($endDate)) {
                        Log::warning('Rentang kustom tidak valid, kembali ke default semua periode.', ['start' => $customStartDate, 'end' => $customEndDate]);
                        $periodType        = 'all';
                        $startDate         = null;
                        $endDate           = null;
                        $reportPeriodLabel = 'Semua Periode (Filter Kustom Tidak Valid)';
                    } else {
                        $reportPeriodLabel = 'Kustom: ' . $startDate->locale('id')->translatedFormat('d M Y') . ' s.d. ' . $endDate->locale('id')->translatedFormat('d M Y');
                    }
                } else {
                    $periodType        = 'all';
                    $startDate         = null;
                    $endDate           = null;
                    $reportPeriodLabel = 'Semua Periode (Filter Kustom Tidak Lengkap)';
                }
            } else {
                $periodType        = 'all';
                $startDate         = null;
                $endDate           = null;
                $reportPeriodLabel = 'Semua Periode';
            }
        } catch (\Exception $e) {
            Log::error('Error parsing date for period filter: ' . $e->getMessage(), ['request_all' => $request->all(), 'prefix' => $dateColumnPrefix]);
            $periodType        = 'all';
            $startDate         = null;
            $endDate           = null;
            $reportPeriodLabel = 'Semua Periode (Error Filter Tanggal)';
        }

        return compact(
            'startDate', 'endDate', 'reportPeriodLabel',
            'periodType', 'selectedDate', 'selectedMonthYear', 'selectedYearOnly',
            'customStartDate',
            'customEndDate'
        );
    }

    // --- Laporan Pembayaran Pelanggan (Pivot Bulanan - Filter Sendiri) ---
    private function getCustomerPaymentReportData(Request $request, $forExport = false)
    {
        $currentCarbon       = Carbon::now();
        $requestedYear       = $request->input('year');
        $requestedStartMonth = $request->input('start_month');
        $requestedEndMonth   = $request->input('end_month');

        $defaultYearHtml       = $currentCarbon->year;
        $defaultStartMonthHtml = 1;
        $defaultEndMonthHtml   = 12;

        $selectedYear       = $request->filled('year') ? (int) $requestedYear : ($forExport ? $currentCarbon->year : $defaultYearHtml);
        $selectedStartMonth = $request->filled('start_month') ? (int) $requestedStartMonth : ($forExport ? 1 : $defaultStartMonthHtml);
        $selectedEndMonth   = $request->filled('end_month') ? (int) $requestedEndMonth : ($forExport ? 12 : $defaultEndMonthHtml);

        $availableYears = [];
        for ($year = $currentCarbon->year + 1; $year >= $currentCarbon->year - 4; $year--) {$availableYears[$year] = $year;}
        $allMonthNames = [];
        for ($m = 1; $m <= 12; $m++) {$allMonthNames[$m] = Carbon::create()->month($m)->locale('id')->translatedFormat('F');}

        $reportDataProcessed            = collect();
        $customersCollectionOrPaginator = collect();
        $displayedMonths                = [];

        if ($selectedYear && $selectedStartMonth && $selectedEndMonth) {
            if ($selectedStartMonth > $selectedEndMonth) {$temp = $selectedStartMonth;
                $selectedStartMonth              = $selectedEndMonth;
                $selectedEndMonth                = $temp;}
            for ($m = $selectedStartMonth; $m <= $selectedEndMonth; $m++) {if (isset($allMonthNames[$m])) {$displayedMonths[$m] = $allMonthNames[$m];}}

            $targetPeriodStart = Carbon::create($selectedYear, $selectedStartMonth, 1)->startOfMonth();
            $targetPeriodEnd   = Carbon::create($selectedYear, $selectedEndMonth, 1)->endOfMonth();

            $customersQuery = Customer::with(['paket', 'payments' => function ($query) use ($targetPeriodStart, $targetPeriodEnd) {
                $query->where(function ($q) use ($targetPeriodStart, $targetPeriodEnd) {
                    $q->where('periode_tagihan_mulai', '<=', $targetPeriodEnd)->where('periode_tagihan_selesai', '>=', $targetPeriodStart);
                })->orderBy('periode_tagihan_mulai', 'asc');
            }])->orderBy('nama_customer', 'asc');

            $customersData                  = $forExport ? $customersQuery->get() : $customersQuery->paginate(10)->withQueryString();
            $customersCollectionOrPaginator = $customersData;

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
                                $statusText                             = 'Lunas';} elseif ($payment->status_pembayaran == 'unpaid') {if (Carbon::parse($payment->tanggal_jatuh_tempo)->isPast() && $currentIterationMonthStart->gte($periodeMulai) && ! $periodeSelesai->isPast()) {$statusClass = 'status-menunggak';
                                $statusText                             = 'Menunggak';} else { $statusClass = 'status-unpaid';
                                $statusText                              = 'Belum Bayar';}} elseif ($payment->status_pembayaran == 'pending_confirmation') {$statusClass = 'status-pending_confirmation';
                                $statusText                             = 'Pending';} elseif ($payment->status_pembayaran == 'failed') {$statusClass = 'status-failed';} elseif ($payment->status_pembayaran == 'cancelled') {$statusClass = 'status-cancelled';}
                            $statusForThisMonth = ['text' => $statusText, 'class' => $statusClass, 'tgl_bayar' => $payment->status_pembayaran == 'paid' && $payment->tanggal_pembayaran ? Carbon::parse($payment->tanggal_pembayaran)->format('d/m/Y') : null, 'invoice_no' => $payment->nomor_invoice, 'payment_id' => $payment->id_payment];
                            break;
                        }
                    }
                    $monthlyStatus[$monthName] = $statusForThisMonth;
                }
                $latestOverallPaidPayment = $customer->payments()->where('status_pembayaran', 'paid')->orderBy('periode_tagihan_selesai', 'desc')->first();
                $tglLayananHabisTerakhir  = $latestOverallPaidPayment ? Carbon::parse($latestOverallPaidPayment->periode_tagihan_selesai) : null;
                $reportDataProcessed->push(['customer' => $customer, 'paket_info' => $customer->paket ? ($customer->paket->kecepatan_paket . ' (Rp ' . number_format($customer->paket->harga_paket, 0, ',', '.') . ')') : '-', 'tgl_aktivasi' => $customer->tanggal_aktivasi ? Carbon::parse($customer->tanggal_aktivasi)->locale('id')->translatedFormat('d M Y') : '-', 'tgl_layanan_habis_terakhir_visual' => $tglLayananHabisTerakhir ? $tglLayananHabisTerakhir->copy()->addDay()->locale('id')->translatedFormat('d M Y') : 'N/A', 'tgl_layanan_habis_sebenarnya' => $tglLayananHabisTerakhir ? $tglLayananHabisTerakhir->locale('id')->translatedFormat('d M Y') : null, 'monthly_status' => $monthlyStatus]);
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

    public function customerPaymentReport(Request $request)
    {
        $pageTitle = 'Laporan Pembayaran Pelanggan';
        $data      = $this->getCustomerPaymentReportData($request, false);
        // Memastikan nilai filter yang digunakan (termasuk default) dikirim ke view
        $data['selectedYear']       = $request->input('year', Carbon::now()->year);
        $data['selectedStartMonth'] = $request->input('start_month', 1);
        $data['selectedEndMonth']   = $request->input('end_month', 12);
        return view('reports.customer_payment_report', array_merge(['pageTitle' => $pageTitle], $data));
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
        $pageTitle  = 'Laporan Keuangan Pendapatan';
        $periodData = $this->determinePeriodRange($request);

        $paymentsQuery = Payment::where('status_pembayaran', 'paid');
        if ($periodData['startDate'] && $periodData['endDate']) {
            $paymentsQuery->whereBetween('tanggal_pembayaran', [$periodData['startDate']->toDateString(), $periodData['endDate']->toDateString()]);
        }

        $totalIncome   = (clone $paymentsQuery)->sum('jumlah_tagihan');
        $incomeByPaket = (clone $paymentsQuery)->join('pakets', 'payments.paket_id', '=', 'pakets.id_paket')
            ->select('pakets.kecepatan_paket', DB::raw('SUM(payments.jumlah_tagihan) as total'), DB::raw('COUNT(payments.id_payment) as transaction_count'))
            ->groupBy('pakets.id_paket', 'pakets.kecepatan_paket')->orderBy('pakets.harga_paket')->get();
        $incomeByMethodData = (clone $paymentsQuery)->leftJoin('ewallets', 'payments.ewallet_id', '=', 'ewallets.id_ewallet')
            ->select('payments.metode_pembayaran', 'ewallets.nama_ewallet', DB::raw('SUM(payments.jumlah_tagihan) as total'), DB::raw('COUNT(payments.id_payment) as transaction_count'))
            ->groupBy('payments.metode_pembayaran', 'ewallets.nama_ewallet')->orderBy('payments.metode_pembayaran')->get();
        $incomeByMethod = collect();
        foreach ($incomeByMethodData as $item) {
            $methodName = Str::title($item->metode_pembayaran ?? 'Tidak Diketahui');
            if ($item->metode_pembayaran == 'transfer' && $item->nama_ewallet) {
                $methodName = 'Transfer via ' . Str::title($item->nama_ewallet);
            }
            if ($incomeByMethod->has($methodName)) {
                $existing                    = $incomeByMethod[$methodName];
                $incomeByMethod[$methodName] = ['total' => $existing['total'] + $item->total, 'count' => $existing['count'] + $item->transaction_count];
            } else {
                $incomeByMethod[$methodName] = ['total' => $item->total, 'count' => $item->transaction_count];
            }
        }
        $previousPeriodIncome = null;
        if ($periodData['periodType'] !== 'custom' && $periodData['periodType'] !== 'all') {
            $prevStartDate = null;
            $prevEndDate   = null;
            switch ($periodData['periodType']) {
                case 'daily':$prevStartDate = $periodData['startDate']->copy()->subDay();
                    $prevEndDate                = $periodData['endDate']->copy()->subDay();
                    break;
                case 'weekly':$prevStartDate = $periodData['startDate']->copy()->subWeek();
                    $prevEndDate                 = $periodData['endDate']->copy()->subWeek();
                    break;
                case 'monthly':$prevStartDate = $periodData['startDate']->copy()->subMonthNoOverflow();
                    $prevEndDate                  = $prevStartDate->copy()->endOfMonth();
                    break;
                case 'yearly':$prevStartDate = $periodData['startDate']->copy()->subYearNoOverflow();
                    $prevEndDate                 = $prevStartDate->copy()->endOfYear();
                    break;
            }
            if ($prevStartDate && $prevEndDate) {
                $previousPeriodIncome = Payment::where('status_pembayaran', 'paid')->whereBetween('tanggal_pembayaran', [$prevStartDate, $prevEndDate])->sum('jumlah_tagihan');
            }
        }

        // Data untuk dropdown filter di view
        $currentCarbon  = Carbon::now();
        $availableYears = [];
        for ($year = $currentCarbon->year + 1; $year >= $currentCarbon->year - 4; $year--) {$availableYears[$year] = $year;}
        $allMonthNames = [];
        for ($m = 1; $m <= 12; $m++) {$allMonthNames[$m] = Carbon::create()->month($m)->locale('id')->translatedFormat('F');}

        return array_merge(
            ['pageTitle'           => $pageTitle, // pageTitle sudah ada di sini
                'totalIncome'          => $totalIncome,
                'incomeByPaket'        => $incomeByPaket,
                'incomeByMethod'       => $incomeByMethod,
                'previousPeriodIncome' => $previousPeriodIncome,
                'availableYears'       => $availableYears, // Untuk partial filter
                'allMonthNames'        => $allMonthNames,  // Untuk partial filter
                'request'              => $request,        // Kirim objek request
            ],
            $periodData // Ini sudah berisi periodType, selectedDate, dll.
        );
    }

    /**
     * Menampilkan Laporan Keuangan Pendapatan (HTML).
     */
    public function financialReport(Request $request)
    {
        $pageTitle  = 'Laporan Keuangan Pendapatan';
        $periodData = $this->determinePeriodRange($request);

        $paymentsQuery = Payment::where('status_pembayaran', 'paid');
        if ($periodData['startDate'] && $periodData['endDate']) {
            $paymentsQuery->whereBetween('tanggal_pembayaran', [$periodData['startDate']->toDateString(), $periodData['endDate']->toDateString()]);
        }

        $totalIncome   = (clone $paymentsQuery)->sum('jumlah_tagihan');
        $incomeByPaket = (clone $paymentsQuery)->join('pakets', 'payments.paket_id', '=', 'pakets.id_paket')
            ->select('pakets.kecepatan_paket', DB::raw('SUM(payments.jumlah_tagihan) as total'), DB::raw('COUNT(payments.id_payment) as transaction_count'))
            ->groupBy('pakets.id_paket', 'pakets.kecepatan_paket')->orderBy('pakets.harga_paket')->get();
        $incomeByMethodData = (clone $paymentsQuery)->leftJoin('ewallets', 'payments.ewallet_id', '=', 'ewallets.id_ewallet')
            ->select('payments.metode_pembayaran', 'ewallets.nama_ewallet', DB::raw('SUM(payments.jumlah_tagihan) as total'), DB::raw('COUNT(payments.id_payment) as transaction_count'))
            ->groupBy('payments.metode_pembayaran', 'ewallets.nama_ewallet')->orderBy('payments.metode_pembayaran')->get();
        $incomeByMethod = collect();
        foreach ($incomeByMethodData as $item) {
            $methodName = Str::title($item->metode_pembayaran ?? 'Tidak Diketahui');
            if ($item->metode_pembayaran == 'transfer' && $item->nama_ewallet) {
                $methodName = 'Transfer via ' . Str::title($item->nama_ewallet);
            }
            if ($incomeByMethod->has($methodName)) {
                $existing                    = $incomeByMethod[$methodName];
                $incomeByMethod[$methodName] = ['total' => $existing['total'] + $item->total, 'count' => $existing['count'] + $item->transaction_count];
            } else {
                $incomeByMethod[$methodName] = ['total' => $item->total, 'count' => $item->transaction_count];
            }
        }
        $previousPeriodIncome = null;
        if ($periodData['periodType'] !== 'custom' && $periodData['periodType'] !== 'all') {
            $prevStartDate = null;
            $prevEndDate   = null;
            switch ($periodData['periodType']) {
                case 'daily':$prevStartDate = $periodData['startDate']->copy()->subDay();
                    $prevEndDate                = $periodData['endDate']->copy()->subDay();
                    break;
                case 'weekly':$prevStartDate = $periodData['startDate']->copy()->subWeek();
                    $prevEndDate                 = $periodData['endDate']->copy()->subWeek();
                    break;
                case 'monthly':$prevStartDate = $periodData['startDate']->copy()->subMonthNoOverflow();
                    $prevEndDate                  = $prevStartDate->copy()->endOfMonth();
                    break;
                case 'yearly':$prevStartDate = $periodData['startDate']->copy()->subYearNoOverflow();
                    $prevEndDate                 = $prevStartDate->copy()->endOfYear();
                    break;
            }
            if ($prevStartDate && $prevEndDate) {
                $previousPeriodIncome = Payment::where('status_pembayaran', 'paid')->whereBetween('tanggal_pembayaran', [$prevStartDate, $prevEndDate])->sum('jumlah_tagihan');
            }
        }
        // Kirim $availableYears dan $allMonthNames untuk partial filter
        $currentCarbon  = Carbon::now();
        $availableYears = [];
        for ($year = $currentCarbon->year + 1; $year >= $currentCarbon->year - 4; $year--) {$availableYears[$year] = $year;}
        $allMonthNames = [];
        for ($m = 1; $m <= 12; $m++) {$allMonthNames[$m] = Carbon::create()->month($m)->locale('id')->translatedFormat('F');}

        return view('reports.financial_report', array_merge(
            ['pageTitle'     => $pageTitle, 'totalIncome'               => $totalIncome, 'incomeByPaket' => $incomeByPaket,
                'incomeByMethod' => $incomeByMethod, 'previousPeriodIncome' => $previousPeriodIncome,
                'availableYears' => $availableYears, 'allMonthNames'        => $allMonthNames,
                'request'        => $request], // <<---- TAMBAHKAN INI
            $periodData
        ));
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

    private function getAllInvoicesReportData(Request $request, $forExport = false)
    {
        $customersForFilter       = Customer::orderBy('nama_customer', 'asc')->get(['id_customer', 'nama_customer']);
        $paketsForFilter          = Paket::orderBy('kecepatan_paket', 'asc')->get(['id_paket', 'kecepatan_paket']);
        $paymentStatusesForFilter = [
            'unpaid' => 'Belum Bayar', 'pending_confirmation' => 'Menunggu Konfirmasi',
            'paid'   => 'Lunas', 'failed'                     => 'Gagal', 'cancelled' => 'Dibatalkan',
        ];

        $creationPeriodData = $this->determinePeriodRange($request, 'creation_');

        $query = Payment::with(['customer:id_customer,nama_customer', 'paket:id_paket,kecepatan_paket', 'ewallet:id_ewallet,nama_ewallet', 'pembuatTagihan', 'pengonfirmasiPembayaran'])
            ->select([
                'id_payment', 'nomor_invoice', 'customer_id', 'paket_id', 'jumlah_tagihan',
                'durasi_pembayaran_bulan', 'periode_tagihan_mulai', 'periode_tagihan_selesai',
                'tanggal_jatuh_tempo', 'tanggal_pembayaran', 'metode_pembayaran', 'ewallet_id',
                'status_pembayaran', 'created_at', 'updated_at',
                'created_by_user_id', 'confirmed_by_user_id', 'catatan_admin',
            ])
            ->orderBy($request->input('sort_by', 'payments.created_at'), $request->input('sort_direction', 'desc'));

        // Filter standar
        if ($request->filled('status_pembayaran')) {
            $query->where('payments.status_pembayaran', $request->status_pembayaran);
        }

        if ($request->filled('customer_id')) {
            $query->where('payments.customer_id', $request->customer_id);
        }

        if ($request->filled('paket_id')) {
            $query->where('payments.paket_id', $request->paket_id);
        }

        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $query->where(function ($q) use ($search) {
                $q->where('payments.nomor_invoice', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('nama_customer', 'like', "%{$search}%")
                            ->orWhere('id_customer', 'like', "%{$search}%");
                    });
            });
        }

        if ($creationPeriodData['startDate'] && $creationPeriodData['endDate']) {
            $query->whereBetween('payments.created_at', [$creationPeriodData['startDate']->toDateString(), $creationPeriodData['endDate']->toDateString()]);
        }

        $summaryQueryBuilder = DB::table('payments');
        if ($creationPeriodData['startDate'] && $creationPeriodData['endDate']) {
            $summaryQueryBuilder->whereBetween('created_at', [$creationPeriodData['startDate']->toDateString(), $creationPeriodData['endDate']->toDateString()]);
        }
        if ($request->filled('status_pembayaran')) {
            $summaryQueryBuilder->where('status_pembayaran', $request->status_pembayaran);
        }

        if ($request->filled('customer_id')) {
            $summaryQueryBuilder->where('customer_id', $request->customer_id);
        }

        if ($request->filled('paket_id')) {
            $summaryQueryBuilder->where('paket_id', $request->paket_id);
        }

        if ($request->filled('search_query')) {
            $summaryQueryBuilder->where('nomor_invoice', 'like', '%' . $request->search_query . '%');
        }

        $totalInvoices   = (clone $summaryQueryBuilder)->count();
        $summaryByStatus = (clone $summaryQueryBuilder)
            ->select('status_pembayaran', DB::raw('COUNT(*) as count'), DB::raw('SUM(jumlah_tagihan) as total_amount'))
            ->groupBy('status_pembayaran')->get()->keyBy('status_pembayaran')->map(function ($item, $key) use ($paymentStatusesForFilter) {
            $item->label = $paymentStatusesForFilter[$key] ?? Str::title(str_replace('_', ' ', $key));
            return $item;
        });
        $totalAmountAll = (clone $summaryQueryBuilder)->sum('jumlah_tagihan');

        $payments = $forExport ? $query->get() : $query->paginate(20)->withQueryString();

        $filterInfo = [
            'creation_period_label' => $creationPeriodData['reportPeriodLabel'],
            'status_pembayaran'     => $request->status_pembayaran ? ($paymentStatusesForFilter[$request->status_pembayaran] ?? $request->status_pembayaran) : null,
            'customer_name'         => $request->customer_id ? ($customersForFilter->firstWhere('id_customer', $request->customer_id)->nama_customer ?? $request->customer_id) : null,
            'paket_info'            => $request->paket_id ? ($paketsForFilter->firstWhere('id_paket', $request->paket_id)->kecepatan_paket ?? $request->paket_id) : null,
            'search_query'          => $request->search_query,
        ];

        $returnData = [
            'payments'        => $payments, 'customers'              => $customersForFilter,
            'pakets'          => $paketsForFilter, 'paymentStatuses' => $paymentStatusesForFilter,
            'request'         => $request, 'totalInvoices'           => $totalInvoices,
            'summaryByStatus' => $summaryByStatus, 'totalAmountAll'  => $totalAmountAll,
            'filterInfo'      => $filterInfo,
        ];
        $currentCarbon  = Carbon::now(); // Untuk availableYears dan allMonthNames di partial
        $availableYears = [];
        for ($year = $currentCarbon->year + 1; $year >= $currentCarbon->year - 4; $year--) {$availableYears[$year] = $year;}
        $allMonthNames = [];
        for ($m = 1; $m <= 12; $m++) {$allMonthNames[$m] = Carbon::create()->month($m)->locale('id')->translatedFormat('F');}
        $returnData['availableYears'] = $availableYears;
        $returnData['allMonthNames']  = $allMonthNames;

        foreach ($creationPeriodData as $key => $value) {
            $returnData['creation_' . $key] = $value;
        }
        return $returnData;
    }

    // ... (method allInvoicesReport, exportAllInvoicesReportPdf, exportAllInvoicesReportExcel tetap ada di sini) ...
    public function allInvoicesReport(Request $request)
    {
        $pageTitle = 'Laporan Semua Tagihan';
        $data      = $this->getAllInvoicesReportData($request, false);
        return view('reports.all_invoices_report', array_merge(['pageTitle' => $pageTitle], $data));
    }

    public function exportAllInvoicesReportPdf(Request $request)
    {
        $pageTitle = 'Laporan Semua Tagihan';
        $data      = $this->getAllInvoicesReportData($request, true);
        if ($data['payments']->isEmpty() && ! $request->hasAny(['start_date', 'end_date', 'status_pembayaran', 'customer_id', 'paket_id', 'search_query'])) {
            return redirect()->route('reports.invoices.all', $request->query())->with('error', 'Tidak ada data untuk diexport atau filter belum diterapkan.');
        }
        $pdf = PDF::loadView('reports.all_invoices_report_pdf', array_merge(['pageTitle' => $pageTitle], $data));
        $pdf->setPaper('a4', 'landscape');
        $fileName = 'laporan_semua_tagihan_';
        $datePart = [];
        if ($request->filled('start_date')) {
            $datePart[] = Carbon::parse($request->start_date)->format('Ymd');
        }

        if ($request->filled('end_date')) {
            $fileName .= ($request->filled('start_date') ? '-sd-' : '') . Carbon::parse($request->end_date)->format('Ymd');
        }

        if (! $request->filled('start_date') && ! $request->filled('end_date')) {
            $fileName .= Carbon::now()->format('YmdHis');
        }

        if ($request->filled('status_pembayaran')) {
            $fileName .= '_status-' . Str::slug($request->status_pembayaran);
        }

        $fileName .= '.pdf';
        return $pdf->download($fileName);
    }

    public function exportAllInvoicesReportExcel(Request $request)
    {
        $pageTitle = 'Laporan Semua Tagihan';
        $data      = $this->getAllInvoicesReportData($request, true);
        if ($data['payments']->isEmpty() && ! $request->hasAny(['start_date', 'end_date', 'status_pembayaran', 'customer_id', 'paket_id', 'search_query'])) {
            return redirect()->route('reports.invoices.all', $request->query())->with('error', 'Tidak ada data untuk diexport atau filter belum diterapkan.');
        }
        $fileName = 'laporan_semua_tagihan_';
        if ($request->filled('start_date')) {
            $fileName .= Carbon::parse($request->start_date)->format('Ymd');
        }

        if ($request->filled('end_date')) {
            $fileName .= ($request->filled('start_date') ? '-sd-' : '') . Carbon::parse($request->end_date)->format('Ymd');
        }

        if (! $request->filled('start_date') && ! $request->filled('end_date')) {
            $fileName .= Carbon::now()->format('YmdHis');
        }

        if ($request->filled('status_pembayaran')) {
            $fileName .= '_status-' . Str::slug($request->status_pembayaran);
        }

        $fileName .= '.xlsx';
        return Excel::download(new AllInvoicesReportExport(array_merge(['pageTitle' => $pageTitle], $data)), $fileName);
    }

    private function getCustomerProfileReportData(Request $request, bool $forExport = false)
    {
        $currentCarbon     = Carbon::now();
        $paketsForFilter   = Paket::orderBy('harga_paket', 'asc')->get(['id_paket', 'kecepatan_paket', 'harga_paket']);
        $statusesForFilter = [
            'baru'      => 'Baru', 'belum'                 => 'Belum Diproses', 'proses' => 'Proses Pemasangan',
            'terpasang' => 'Terpasang (Aktif)', 'nonaktif' => 'Nonaktif',
        ];

        $activationPeriodData = $this->determinePeriodRange($request, 'activation_');

        $query = Customer::with(['paket', 'deviceSn.deviceModel'])->orderBy('nama_customer', 'asc');

        // Filter standar
        $searchQueryInput = $request->input('search_query');
        $filterStatus     = $request->input('status_pelanggan');
        $filterPaketId    = $request->input('paket_id');

        if ($searchQueryInput) {
            $query->where(function ($q) use ($searchQueryInput) {
                $q->where('nama_customer', 'like', '%' . $searchQueryInput . '%')
                    ->orWhere('id_customer', 'like', '%' . $searchQueryInput . '%')
                    ->orWhere('nik_customer', 'like', '%' . $searchQueryInput . '%')
                    ->orWhere('wa_customer', 'like', '%' . $searchQueryInput . '%')
                    ->orWhere('active_user', 'like', '%' . $searchQueryInput . '%');
            });
        }
        if ($filterStatus && $filterStatus != '') {$query->where('status', $filterStatus);}
        if ($filterPaketId && $filterPaketId != '') {$query->where('paket_id', $filterPaketId);}

        if ($activationPeriodData['startDate'] && $activationPeriodData['endDate']) {
            $query->whereBetween('tanggal_aktivasi', [$activationPeriodData['startDate']->toDateString(), $activationPeriodData['endDate']->toDateString()]);
        }

        $customersData = $forExport ? $query->get() : $query->paginate(25)->withQueryString();

        $summaryBaseQuery = Customer::query();
        if ($searchQueryInput) {$summaryBaseQuery->where(function ($q) use ($searchQueryInput) {$q->where('nama_customer', 'like', '%' . $searchQueryInput . '%')->orWhere('id_customer', 'like', '%' . $searchQueryInput . '%')->orWhere('nik_customer', 'like', '%' . $searchQueryInput . '%')->orWhere('wa_customer', 'like', '%' . $searchQueryInput . '%')->orWhere('active_user', 'like', '%' . $searchQueryInput . '%');});}
        if ($filterStatus && $filterStatus != '') {$summaryBaseQuery->where('status', $filterStatus);}
        if ($filterPaketId && $filterPaketId != '') {$summaryBaseQuery->where('paket_id', $filterPaketId);}
        if ($activationPeriodData['startDate'] && $activationPeriodData['endDate']) {$summaryBaseQuery->whereBetween('tanggal_aktivasi', [$activationPeriodData['startDate']->toDateString(), $activationPeriodData['endDate']->toDateString()]);}

        $totalCustomersFiltered = (clone $summaryBaseQuery)->count();
        $summaryByStatus        = (clone $summaryBaseQuery)->select('status', DB::raw('count(*) as total'))->groupBy('status')->get()->mapWithKeys(function ($item) use ($statusesForFilter) {
            return [$item->status => ['label' => $statusesForFilter[$item->status] ?? Str::title(str_replace('_', ' ', $item->status)), 'total' => $item->total]];
        });

        $summaryByPaketQuery = DB::table('customers as c')->join('pakets as p', 'c.paket_id', '=', 'p.id_paket');
        if ($filterStatus && $filterStatus != '') {
            $summaryByPaketQuery->where('c.status', $filterStatus);
        }

        if ($filterPaketId && $filterPaketId != '') {
            $summaryByPaketQuery->where('c.paket_id', $filterPaketId);
        }

        if ($activationPeriodData['startDate'] && $activationPeriodData['endDate']) {
            $summaryByPaketQuery->whereBetween('c.tanggal_aktivasi', [$activationPeriodData['startDate']->toDateString(), $activationPeriodData['endDate']->toDateString()]);
        }
        if ($searchQueryInput) { /* ... (penanganan search_query untuk summaryByPaket) ... */}
        $summaryByPaket = $summaryByPaketQuery->select('p.kecepatan_paket', DB::raw('count(c.id_customer) as total'))
            ->groupBy('p.id_paket', 'p.kecepatan_paket')->orderBy('total', 'desc')->get();

        $customerGrowth = ['label' => '', 'count' => 0, 'percentage' => null, 'previous_count' => 0];

        if ($activationPeriodData['startDate'] && $activationPeriodData['endDate'] && $activationPeriodData['periodType'] !== 'all') {
            $currentPeriodQuery = Customer::query();
            // Terapkan filter standar (status, paket) ke query pertumbuhan juga agar perbandingannya adil
            if ($request->filled('status_pelanggan')) {$currentPeriodQuery->where('status', $request->status_pelanggan);}
            if ($request->filled('paket_id')) {$currentPeriodQuery->where('paket_id', $request->paket_id);}
            $currentPeriodQuery->whereBetween('tanggal_aktivasi', [$activationPeriodData['startDate']->toDateString(), $activationPeriodData['endDate']->toDateString()]);

            $newCustomersThisPeriod     = $currentPeriodQuery->count();
            $newCustomersPreviousPeriod = 0;
            $previousPeriodQuery        = Customer::query();
            if ($request->filled('status_pelanggan')) {$previousPeriodQuery->where('status', $request->status_pelanggan);}
            if ($request->filled('paket_id')) {$previousPeriodQuery->where('paket_id', $request->paket_id);}

            if ($activationPeriodData['periodType'] === 'monthly') {
                $prevMonthStart             = $activationPeriodData['startDate']->copy()->subMonthNoOverflow()->startOfMonth();
                $prevMonthEnd               = $prevMonthStart->copy()->endOfMonth();
                $customerGrowth['label']    = "vs " . $prevMonthStart->locale('id')->translatedFormat('F Y');
                $newCustomersPreviousPeriod = $previousPeriodQuery->whereBetween('tanggal_aktivasi', [$prevMonthStart, $prevMonthEnd])->count();
            } elseif ($activationPeriodData['periodType'] === 'yearly') {
                $prevYearStart              = $activationPeriodData['startDate']->copy()->subYearNoOverflow()->startOfYear();
                $prevYearEnd                = $prevYearStart->copy()->endOfYear();
                $customerGrowth['label']    = "vs Tahun " . $prevYearStart->year;
                $newCustomersPreviousPeriod = $previousPeriodQuery->whereBetween('tanggal_aktivasi', [$prevYearStart, $prevYearEnd])->count();
            } elseif ($activationPeriodData['periodType'] === 'daily') {
                $prevDay                    = $activationPeriodData['startDate']->copy()->subDay();
                $customerGrowth['label']    = "vs " . $prevDay->locale('id')->translatedFormat('d F Y');
                $newCustomersPreviousPeriod = $previousPeriodQuery->whereDate('tanggal_aktivasi', $prevDay)->count();
            } elseif ($activationPeriodData['periodType'] === 'weekly') {
                $prevWeekEnd                = $activationPeriodData['startDate']->copy()->subDay()->endOfDay();
                $prevWeekStart              = $prevWeekEnd->copy()->subDays(6)->startOfDay();
                $customerGrowth['label']    = "vs minggu sebelumnya";
                $newCustomersPreviousPeriod = $previousPeriodQuery->whereBetween('tanggal_aktivasi', [$prevWeekStart, $prevWeekEnd])->count();
            }

            $customerGrowth['count']          = $newCustomersThisPeriod;
            $customerGrowth['previous_count'] = $newCustomersPreviousPeriod;
            if ($newCustomersPreviousPeriod > 0) {
                $customerGrowth['percentage'] = (($newCustomersThisPeriod - $newCustomersPreviousPeriod) / $newCustomersPreviousPeriod) * 100;
            } elseif ($newCustomersThisPeriod > 0 && $newCustomersPreviousPeriod == 0) {
                $customerGrowth['percentage'] = 100;
            }
        }
        // Perbaikan filter info
        $filterInfo = [];
        
        // Search query filter
        if ($searchQueryInput) {
            $filterInfo['search_query'] = $searchQueryInput;
        }

        // Status filter 
        if ($filterStatus) {
            $filterInfo['status_pelanggan'] = $statusesForFilter[$filterStatus] ?? Str::title(str_replace('_', ' ', $filterStatus));
        }

        // Paket filter
        if ($filterPaketId) {
            $paketInfo = $paketsForFilter->firstWhere('id_paket', $filterPaketId);
            $filterInfo['paket_info'] = $paketInfo ? $paketInfo->kecepatan_paket : null;
        }

        // Period filter
        if ($activationPeriodData['startDate'] && $activationPeriodData['endDate']) {
            $filterInfo['activation_reportPeriodLabel'] = $activationPeriodData['reportPeriodLabel'];
            $filterInfo['activation_periodType'] = $activationPeriodData['periodType'];
        }

        $returnData = [
            'customers' => $customersData,
            'pakets' => $paketsForFilter,
            'statuses' => $statusesForFilter,
            'request' => $request,
            'totalCustomersFiltered' => $totalCustomersFiltered,
            'summaryByStatus' => $summaryByStatus,
            'summaryByPaket' => $summaryByPaket,
            'customerGrowth' => $customerGrowth,
            'filterInfo' => $filterInfo // Tambahkan filterInfo ke returnData
        ];

        $availableYears = [];
        for ($year = $currentCarbon->year + 1; $year >= $currentCarbon->year - 4; $year--) {$availableYears[$year] = $year;}
        $allMonthNames = [];
        for ($m = 1; $m <= 12; $m++) {$allMonthNames[$m] = Carbon::create()->month($m)->locale('id')->translatedFormat('F');}
        $returnData['availableYears'] = $availableYears;
        $returnData['allMonthNames']  = $allMonthNames;

        foreach ($activationPeriodData as $key => $value) {
            $returnData['activation_' . $key] = $value;
        }
        return $returnData;

    }

    public function customerProfileReport(Request $request)
    {
        $pageTitle = 'Laporan Data Pelanggan';
        // Panggil helper untuk mendapatkan semua data yang dibutuhkan
        $data = $this->getCustomerProfileReportData($request, false);

        // $data sudah berisi semua kunci yang benar ('customers', 'pakets', 'statuses', dll.)
        // Jadi, tidak perlu lagi mengganti nama kunci di sini.
        return view('reports.customer_profile_report', array_merge(['pageTitle' => $pageTitle], $data));
    }

    public function exportCustomerProfileReportPdf(Request $request)
    {
        $pageTitle = 'Laporan Data Pelanggan';
        $data      = $this->getCustomerProfileReportData($request, true);

        if ($data['customers']->isEmpty() && ! $request->hasAny(['search_query', 'status_pelanggan', 'paket_id', 'activation_period_type'])) {
            return redirect()->route('reports.customer_profile', $request->query())->with('error', 'Tidak ada data untuk diexport atau filter belum diterapkan.');
        }

        // $data sudah berisi 'statuses', 'pakets', dll.
        $pdf = PDF::loadView('reports.customer_profile_report_pdf', array_merge(['pageTitle' => $pageTitle], $data));
        $pdf->setPaper('a4', 'landscape');

        $fileName              = 'laporan_data_pelanggan_';
        $activationPeriodLabel = $data['activation_reportPeriodLabel'] ?? 'semua_periode';
        if ($activationPeriodLabel !== 'Semua Periode' && ! Str::contains($activationPeriodLabel, ['Tidak Valid', 'Tidak Lengkap', 'Error Filter'])) {
            $fileName .= Str::slug($activationPeriodLabel, '_');
        } else {
            $fileName .= Carbon::now()->format('YmdHis');
        }
        $fileName .= '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Export Laporan Data Pelanggan ke Excel.
     */
    public function exportCustomerProfileReportExcel(Request $request)
    {
        $pageTitle = 'Laporan Data Pelanggan';
        $data      = $this->getCustomerProfileReportData($request, true);

        if ($data['customers']->isEmpty() && ! $request->hasAny(['search_query', 'status_pelanggan', 'paket_id', 'activation_period_type'])) {
            return redirect()->route('reports.customer_profile', $request->query())->with('error', 'Tidak ada data untuk diexport atau filter belum diterapkan.');
        }

        $fileName              = 'laporan_data_pelanggan_';
        $activationPeriodLabel = $data['activation_reportPeriodLabel'] ?? 'semua_periode';
        if ($activationPeriodLabel !== 'Semua Periode' && ! Str::contains($activationPeriodLabel, ['Tidak Valid', 'Tidak Lengkap', 'Error Filter'])) {
            $fileName .= Str::slug($activationPeriodLabel, '_');
        } else {
            $fileName .= Carbon::now()->format('YmdHis');
        }
        $fileName .= '.xlsx';

        // $data sudah berisi 'statuses', 'pakets', dll.
        return Excel::download(new CustomerProfileReportExport(array_merge(['pageTitle' => $pageTitle], $data)), $fileName);
    }
}
