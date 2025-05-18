<?php
namespace App\Http\Controllers;

use App\Exports\CustomerReportExport;
use App\Models\Customer;
use App\Models\Paket;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon; // Sesuai dengan controller Anda
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function getCustomerReportData(Request $request, $forExport = false)
    {
        $currentCarbon       = Carbon::now();
        $requestedYear       = $request->input('year');
        $requestedStartMonth = $request->input('start_month');
        $requestedEndMonth   = $request->input('end_month');

        $selectedYear       = $requestedYear ? (int) $requestedYear : null;
        $selectedStartMonth = $requestedStartMonth ? (int) $requestedStartMonth : null;
        $selectedEndMonth   = $requestedEndMonth ? (int) $requestedEndMonth : null;

        $availableYears = [];
        for ($year = $currentCarbon->year + 1; $year >= $currentCarbon->year - 4; $year--) {
            $availableYears[$year] = $year;
        }

        $allMonthNames = [];
        for ($m = 1; $m <= 12; $m++) {
            $allMonthNames[$m] = Carbon::create()->month($m)->locale('id')->translatedFormat('F');
        }

                                                     // PASTIKAN INISIALISASI INI ADA DAN BENAR
        $reportDataProcessed            = collect(); // Untuk data yang akan ditampilkan per baris pelanggan
        $customersCollectionOrPaginator = collect(); // Akan diisi oleh ->get() atau ->paginate()
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

            if ($forExport) {
                $customersCollectionOrPaginator = $customersQuery->get();
            } else {
                $customersCollectionOrPaginator = $customersQuery->paginate(10)->withQueryString();
            }

            // Loop menggunakan $customersCollectionOrPaginator
            foreach ($customersCollectionOrPaginator as $customer) {
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

                // Mengisi $reportDataProcessed
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
            'reportData'         => $reportDataProcessed,            // Menggunakan nama baru untuk data yang diproses
            'customersPaginator' => $customersCollectionOrPaginator, // Ini adalah objek paginator atau collection
            'availableYears'     => $availableYears,
            'selectedYear'       => $selectedYear,
            'allMonthNames'      => $allMonthNames,
            'selectedStartMonth' => $selectedStartMonth,
            'selectedEndMonth'   => $selectedEndMonth,
            'displayedMonths'    => $displayedMonths,
        ];
    }

    public function customerReport(Request $request)
    {
        $pageTitle = 'Laporan Pembayaran Pelanggan';
        $data      = $this->getCustomerReportData($request, false);
        return view('reports.customer_report', array_merge(['pageTitle' => $pageTitle], $data));
    }

    public function exportCustomerReportPdf(Request $request)
    {
        $pageTitle = 'Laporan Pembayaran Pelanggan';
        $data      = $this->getCustomerReportData($request, true);
        if ($data['reportData']->isEmpty() && (! $data['selectedYear'] || ! $data['selectedStartMonth'] || ! $data['selectedEndMonth'])) {
            return redirect()->route('reports.customer', $request->query())->with('error', 'Silakan pilih filter tahun dan rentang bulan terlebih dahulu untuk export PDF.');
        }
        $pdf      = PDF::loadView('reports.customer_report_pdf', array_merge(['pageTitle' => $pageTitle], $data))->setPaper('a4', 'landscape');
        $fileName = 'laporan_pelanggan_' . $data['selectedYear'];
        if ($data['selectedStartMonth'] && isset($data['allMonthNames'][$data['selectedStartMonth']])) {
            $fileName .= '_' . Str::slug($data['allMonthNames'][$data['selectedStartMonth']]);
        }
        if ($data['selectedEndMonth'] && $data['selectedEndMonth'] != $data['selectedStartMonth'] && isset($data['allMonthNames'][$data['selectedEndMonth']])) {
            $fileName .= '-sd-' . Str::slug($data['allMonthNames'][$data['selectedEndMonth']]);
        }
        $fileName .= '.pdf';
        return $pdf->download($fileName);
    }

    public function exportCustomerReportExcel(Request $request)
    {
        $data = $this->getCustomerReportData($request, true);
        if ($data['reportData']->isEmpty() && (! $data['selectedYear'] || ! $data['selectedStartMonth'] || ! $data['selectedEndMonth'])) {
            return redirect()->route('reports.customer', $request->query())->with('error', 'Silakan pilih filter tahun dan rentang bulan terlebih dahulu untuk export Excel.');
        }
        $fileName = 'laporan_pelanggan_' . $data['selectedYear'];
        if ($data['selectedStartMonth'] && isset($data['allMonthNames'][$data['selectedStartMonth']])) {
            $fileName .= '_' . Str::slug($data['allMonthNames'][$data['selectedStartMonth']]);
        }
        if ($data['selectedEndMonth'] && $data['selectedEndMonth'] != $data['selectedStartMonth'] && isset($data['allMonthNames'][$data['selectedEndMonth']])) {
            $fileName .= '-sd-' . Str::slug($data['allMonthNames'][$data['selectedEndMonth']]);
        }
        $fileName .= '.xlsx';
        return Excel::download(new CustomerReportExport($data), $fileName);
    }
}
