<?php
namespace App\Http\Controllers;

use App\Exports\AllInvoicesReportExport;
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

    private function getCustomerPaymentReportData(Request $request, $forExport = false)
    {
        $currentCarbon = Carbon::now();

        // Ambil nilai filter dari request. Jika tidak ada, nilai default akan di-set di method pemanggil.
        $selectedYear       = $request->input('year');
        $selectedStartMonth = $request->input('start_month');
        $selectedEndMonth   = $request->input('end_month');

        Log::info("Laporan Pembayaran Pelanggan (getCustomerPaymentReportData): Year={$selectedYear}, StartM={$selectedStartMonth}, EndM={$selectedEndMonth}");

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

        // Hanya proses jika semua filter (tahun, bulan mulai, bulan akhir) sudah valid
        if ($selectedYear && $selectedStartMonth && $selectedEndMonth) {
            // Pastikan start_month tidak lebih besar dari end_month
            if ((int) $selectedStartMonth > (int) $selectedEndMonth) {
                $temp               = $selectedStartMonth;
                $selectedStartMonth = $selectedEndMonth;
                $selectedEndMonth   = $temp;
            }

            for ($m = (int) $selectedStartMonth; $m <= (int) $selectedEndMonth; $m++) {
                if (isset($allMonthNames[$m])) {
                    $displayedMonths[$m] = $allMonthNames[$m];
                }
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
            $customersCollectionOrPaginator = $customersData;

            foreach ($customersData as $customer) {
                $monthlyStatus = [];
                // Loop berdasarkan $displayedMonths yang sudah difilter rentangnya
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
            'selectedYear'       => $selectedYear, // Ini akan berisi nilai dari request atau default dari pemanggil
            'allMonthNames'      => $allMonthNames,
            'selectedStartMonth' => $selectedStartMonth, // Ini akan berisi nilai dari request atau default dari pemanggil
            'selectedEndMonth'   => $selectedEndMonth,   // Ini akan berisi nilai dari request atau default dari pemanggil
            'displayedMonths'    => $displayedMonths,
        ];
    }

    public function customerPaymentReport(Request $request)
    {
        $pageTitle = 'Laporan Pembayaran Pelanggan';

        // Set nilai default jika tidak ada input dari request (untuk tampilan awal)
        $defaultYear       = Carbon::now()->year;
        $defaultStartMonth = 1;  // Januari
        $defaultEndMonth   = 12; // Desember

        // Buat request baru atau modifikasi yang ada untuk dikirim ke getCustomerPaymentReportData
        // Ini memastikan bahwa getCustomerPaymentReportData selalu menerima parameter filter yang valid.
        $modifiedRequest = new Request(array_merge($request->all(), [
            'year'        => $request->input('year', $defaultYear),
            'start_month' => $request->input('start_month', $defaultStartMonth),
            'end_month'   => $request->input('end_month', $defaultEndMonth),
        ]));

        $data = $this->getCustomerPaymentReportData($modifiedRequest, false);

        // Pastikan nilai filter yang aktif (default atau dari user) juga dikirim ke view
        // untuk pre-fill form filter.
        $data['selectedYear']       = $modifiedRequest->input('year');
        $data['selectedStartMonth'] = $modifiedRequest->input('start_month');
        $data['selectedEndMonth']   = $modifiedRequest->input('end_month');

        return view('reports.customer_payment_report', array_merge(['pageTitle' => $pageTitle], $data));
    }

    // ... (method exportCustomerPaymentReportPdf dan exportCustomerPaymentReportExcel menggunakan $modifiedRequest juga jika perlu default) ...
    public function exportCustomerPaymentReportPdf(Request $request)
    {
        $pageTitle         = 'Laporan Pembayaran Pelanggan';
        $defaultYear       = Carbon::now()->year;
        $defaultStartMonth = 1;
        $defaultEndMonth   = 12;

        $modifiedRequest = new Request(array_merge($request->all(), [
            'year'        => $request->input('year', $defaultYear),
            'start_month' => $request->input('start_month', $defaultStartMonth),
            'end_month'   => $request->input('end_month', $defaultEndMonth),
        ]));

        $data = $this->getCustomerPaymentReportData($modifiedRequest, true);

        if ($data['reportData']->isEmpty()) { // Cek jika reportData kosong setelah filter default/user
            return redirect()->route('reports.customer_payment', $request->query())->with('error', 'Tidak ada data untuk diexport pada periode yang dipilih.');
        }

        // Menambahkan data filter yang aktif ke PDF view
        $data['selectedYear']       = $modifiedRequest->input('year');
        $data['selectedStartMonth'] = $modifiedRequest->input('start_month');
        $data['selectedEndMonth']   = $modifiedRequest->input('end_month');

        $pdf      = PDF::loadView('reports.customer_payment_report_pdf', array_merge(['pageTitle' => $pageTitle], $data))->setPaper('a4', 'landscape');
        $fileName = 'laporan_pembayaran_pelanggan_' . $data['selectedYear'];
        if ($data['selectedStartMonth'] && isset($data['allMonthNames'][$data['selectedStartMonth']])) {
            $fileName .= '_' . Str::slug($data['allMonthNames'][$data['selectedStartMonth']]);
        }
        if ($data['selectedEndMonth'] && $data['selectedEndMonth'] != $data['selectedStartMonth'] && isset($data['allMonthNames'][$data['selectedEndMonth']])) {
            $fileName .= '-sd-' . Str::slug($data['allMonthNames'][$data['selectedEndMonth']]);
        }
        $fileName .= '.pdf';
        return $pdf->download($fileName);
    }

    public function exportCustomerPaymentReportExcel(Request $request)
    {
        $pageTitle         = 'Laporan Pembayaran Pelanggan';
        $defaultYear       = Carbon::now()->year;
        $defaultStartMonth = 1;
        $defaultEndMonth   = 12;

        $modifiedRequest = new Request(array_merge($request->all(), [
            'year'        => $request->input('year', $defaultYear),
            'start_month' => $request->input('start_month', $defaultStartMonth),
            'end_month'   => $request->input('end_month', $defaultEndMonth),
        ]));

        $data = $this->getCustomerPaymentReportData($modifiedRequest, true);

        if ($data['reportData']->isEmpty()) {
            return redirect()->route('reports.customer_payment', $request->query())->with('error', 'Tidak ada data untuk diexport pada periode yang dipilih.');
        }

        // Menambahkan data filter yang aktif ke Export Class
        $data['selectedYear']       = $modifiedRequest->input('year');
        $data['selectedStartMonth'] = $modifiedRequest->input('start_month');
        $data['selectedEndMonth']   = $modifiedRequest->input('end_month');

        $fileName = 'laporan_pembayaran_pelanggan_' . $data['selectedYear'];
        if ($data['selectedStartMonth'] && isset($data['allMonthNames'][$data['selectedStartMonth']])) {
            $fileName .= '_' . Str::slug($data['allMonthNames'][$data['selectedStartMonth']]);
        }
        if ($data['selectedEndMonth'] && $data['selectedEndMonth'] != $data['selectedStartMonth'] && isset($data['allMonthNames'][$data['selectedEndMonth']])) {
            $fileName .= '-sd-' . Str::slug($data['allMonthNames'][$data['selectedEndMonth']]);
        }
        $fileName .= '.xlsx';
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

    private function getAllInvoicesReportData(Request $request, $forExport = false)
    {
        $customersForFilter       = Customer::orderBy('nama_customer', 'asc')->get(['id_customer', 'nama_customer']);
        $paketsForFilter          = Paket::orderBy('kecepatan_paket', 'asc')->get(['id_paket', 'kecepatan_paket']);
        $paymentStatusesForFilter = [
            'unpaid' => 'Belum Bayar', 'pending_confirmation' => 'Menunggu Konfirmasi',
            'paid'   => 'Lunas', 'failed'                     => 'Gagal', 'cancelled' => 'Dibatalkan',
        ];

        $filterStartDate  = $request->input('start_date');
        $filterEndDate    = $request->input('end_date');
        $filterStatus     = $request->input('status_pembayaran');
        $filterCustomerId = $request->input('customer_id');
        $filterPaketId    = $request->input('paket_id');
        $searchQueryInput = $request->input('search_query'); // Digunakan untuk pencarian gabungan

        // Query utama untuk daftar tagihan (Eloquent)
        $mainPaymentsQuery = Payment::query()
            ->select([ // Pilih kolom yang dibutuhkan untuk tampilan dan relasi
                'id_payment', 'nomor_invoice', 'customer_id', 'paket_id', 'jumlah_tagihan',
                'durasi_pembayaran_bulan', 'periode_tagihan_mulai', 'periode_tagihan_selesai',
                'tanggal_jatuh_tempo', 'tanggal_pembayaran', 'metode_pembayaran', 'ewallet_id',
                'status_pembayaran', 'created_at', 'updated_at',
                'created_by_user_id', 'confirmed_by_user_id', 'catatan_admin',
            ])
            ->with([
                'customer:id_customer,nama_customer',
                'paket:id_paket,kecepatan_paket',
                'ewallet:id_ewallet,nama_ewallet',
                'pembuatTagihan',          // Tidak memilih kolom spesifik, biarkan Laravel ambil semua
                'pengonfirmasiPembayaran', // Tidak memilih kolom spesifik
            ])
            ->orderBy($request->input('sort_by', 'payments.created_at'), $request->input('sort_direction', 'desc'));

        // Query builder dasar untuk summary (langsung ke tabel 'payments')
        $summaryQueryBuilder = DB::table('payments');

        // Terapkan filter ke kedua query
        $applyFiltersClosure = function ($queryBuilderInstance) use ($request, $filterStartDate, $filterEndDate, $filterStatus, $filterCustomerId, $filterPaketId, $searchQueryInput) {
            if ($filterStartDate) {
                $queryBuilderInstance->whereDate('created_at', '>=', Carbon::parse($filterStartDate)->startOfDay());
            }

            if ($filterEndDate) {
                $queryBuilderInstance->whereDate('created_at', '<=', Carbon::parse($filterEndDate)->endOfDay());
            }

            if ($filterStatus) {
                $queryBuilderInstance->where('status_pembayaran', $filterStatus);
            }

            if ($filterCustomerId) {
                $queryBuilderInstance->where('customer_id', $filterCustomerId);
            }

            if ($filterPaketId) {
                $queryBuilderInstance->where('paket_id', $filterPaketId);
            }

            if ($searchQueryInput) {
                if ($queryBuilderInstance instanceof \Illuminate\Database\Eloquent\Builder) { // Untuk Eloquent
                    $queryBuilderInstance->where(function ($q) use ($searchQueryInput) {
                        $q->where('nomor_invoice', 'like', "%{$searchQueryInput}%")
                            ->orWhereHas('customer', function ($cq) use ($searchQueryInput) {
                                $cq->where('nama_customer', 'like', "%{$searchQueryInput}%")
                                    ->orWhere('id_customer', 'like', "%{$searchQueryInput}%");
                            });
                    });
                } else { // Untuk Query Builder (DB::table)
                             // Untuk summary, kita sederhanakan pencarian hanya pada nomor_invoice
                             // Jika ingin lebih kompleks, perlu join manual di $summaryQueryBuilder
                    $queryBuilderInstance->where('nomor_invoice', 'like', '%' . $searchQueryInput . '%');
                }
            }
        };

        $applyFiltersClosure($mainPaymentsQuery);
        $applyFiltersClosure($summaryQueryBuilder);

        // Hitung Summary/Kesimpulan dari Query Builder
        $totalInvoices = (clone $summaryQueryBuilder)->count();

        $summaryByStatus = (clone $summaryQueryBuilder)
            ->select('status_pembayaran', DB::raw('COUNT(*) as count'), DB::raw('SUM(jumlah_tagihan) as total_amount'))
            ->groupBy('status_pembayaran')
            ->get()
            ->keyBy('status_pembayaran')->map(function ($item, $key) use ($paymentStatusesForFilter) {
            // $item di sini adalah objek stdClass
            $item->label = $paymentStatusesForFilter[$key] ?? Str::title(str_replace('_', ' ', $key));
            return $item;
        });

        $totalAmountAll = (clone $summaryQueryBuilder)->sum('jumlah_tagihan');

        // Ambil data utama (dengan paginasi untuk HTML, semua untuk export)
        $payments = $forExport ? $mainPaymentsQuery->get() : $mainPaymentsQuery->paginate(20)->withQueryString();

        $filterInfo = [
            'start_date'        => $filterStartDate,
            'end_date'          => $filterEndDate,
            'status_pembayaran' => $filterStatus ? ($paymentStatusesForFilter[$filterStatus] ?? $filterStatus) : null,
            'customer_name'     => $filterCustomerId ? (Customer::find($filterCustomerId)->nama_customer ?? $filterCustomerId) : null,
            'paket_info'        => $filterPaketId ? (Paket::find($filterPaketId)->kecepatan_paket ?? $filterPaketId) : null,
            'search_query'      => $searchQueryInput,
        ];

        return [
            'payments'        => $payments,
            'customers'       => $customersForFilter,
            'pakets'          => $paketsForFilter,
            'paymentStatuses' => $paymentStatusesForFilter,
            'request'         => $request,
            'totalInvoices'   => $totalInvoices,
            'summaryByStatus' => $summaryByStatus,
            'totalAmountAll'  => $totalAmountAll,
            'filterInfo'      => $filterInfo,
        ];
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
}
