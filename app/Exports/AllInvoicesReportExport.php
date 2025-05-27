<?php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border as PhpSpreadsheetBorder;
use PhpOffice\PhpSpreadsheet\Style\Fill as PhpSpreadsheetFill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllInvoicesReportExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    protected array $exportDataArray;
    protected string $pageTitle;
    protected array $filterInfo;
    protected int $summaryDataRowCount = 0;
    protected int $summaryHeaderRowPosition = 0;
    protected int $mainTableHeaderRowPosition = 0;
    protected int $summaryTitleRowPosition = 0;
    protected int $mainTableDataStartRow = 0;

    public function __construct(array $dataFromController)
    {
        $this->pageTitle  = $dataFromController['pageTitle'] ?? 'Laporan Semua Tagihan';
        $this->filterInfo = $dataFromController['filterInfo'] ?? [];
        $this->prepareExportDataArray(
            $dataFromController['payments'] ?? collect(),
            $dataFromController['totalInvoices'] ?? 0,
            $dataFromController['totalAmountAll'] ?? 0,
            $dataFromController['summaryByStatus'] ?? collect(),
            $dataFromController['paymentStatusesForFilter'] ?? []
        );
    }

    protected function prepareExportDataArray(iterable $payments, int $totalInvoices, float $totalAmountAll, Collection $summaryByStatus, array $paymentStatusesForFilter): void
    {
        $this->exportDataArray = [];
        $currentBuildRow = 1;

        // Title Row - Start from column B
        $this->exportDataArray[] = ['', $this->pageTitle];
        $currentBuildRow++;

        // Filter Info Row - Start from column B
        $filterText = "Filter Aktif: ";
        $activeFilters = [];
        if (! empty($this->filterInfo['start_date'])) {
            $activeFilters[] = "Dari Tgl Buat: " . Carbon::parse($this->filterInfo['start_date'])->format('d/m/Y');
        }

        if (! empty($this->filterInfo['end_date'])) {
            $activeFilters[] = "Sampai Tgl Buat: " . Carbon::parse($this->filterInfo['end_date'])->format('d/m/Y');
        }

        if (! empty($this->filterInfo['status_pembayaran'])) {
            $activeFilters[] = "Status: " . $this->filterInfo['status_pembayaran'];
        }

        if (! empty($this->filterInfo['customer_name'])) {
            $activeFilters[] = "Pelanggan: " . $this->filterInfo['customer_name'];
        }

        if (! empty($this->filterInfo['paket_info'])) {
            $activeFilters[] = "Paket: " . $this->filterInfo['paket_info'];
        }

        if (! empty($this->filterInfo['search_query'])) {
            $activeFilters[] = "Cari Invoice: " . $this->filterInfo['search_query'];
        }

        $this->exportDataArray[] = ['', !empty($activeFilters) ? $filterText . implode(', ', $activeFilters) : $filterText . "Tidak ada filter"];
        $currentBuildRow++;

        // Empty Row
        $this->exportDataArray[] = ['', ''];
        $currentBuildRow++;

        // Main Summary Section - Start from column B
        $this->summaryTitleRowPosition = $currentBuildRow;
        $this->exportDataArray[] = ['', "RINGKASAN LAPORAN TAGIHAN"];
        $currentBuildRow++;

        // Total Summary - Start from column B
        $this->exportDataArray[] = ['', "Total Invoice", number_format($totalInvoices)];
        $this->exportDataArray[] = ['', "Total Nilai Tagihan", "Rp " . number_format($totalAmountAll, 0, ',', '.')];
        $currentBuildRow += 2;

        // Empty Row
        $this->exportDataArray[] = ['', ''];
        $currentBuildRow++;

        // Status Summary Section - Start from column B
        $this->summaryHeaderRowPosition = $currentBuildRow;
        $this->exportDataArray[] = ['', "DETAIL STATUS PEMBAYARAN"];
        $currentBuildRow++;

        // Status Summary Headers - Start from column B
        $this->exportDataArray[] = ['', "Status", "Jumlah Invoice", "Total Nilai"];
        $currentBuildRow++;

        // Status data - Start from column B
        $statusOrder = ['paid' => 'Lunas', 'unpaid'      => 'Belum Bayar', 'pending_confirmation' => 'Menunggu Konfirmasi',
            'cancelled'            => 'Dibatalkan', 'failed' => 'Gagal'];

        foreach ($statusOrder as $statusKey => $statusLabel) {
            if (isset($summaryByStatus[$statusKey])) {
                $this->exportDataArray[] = [
                    '',
                    $statusLabel,
                    $summaryByStatus[$statusKey]->count,
                    "Rp " . number_format($summaryByStatus[$statusKey]->total_amount, 0, ',', '.')
                ];
                $currentBuildRow++;
            }
        }
        $this->summaryDataRowCount = count($statusOrder);

        // Empty Rows
        $this->exportDataArray[] = ['', ''];
        $this->exportDataArray[] = ['', ''];
        $currentBuildRow += 2;

        // Main Table Headers
        $this->mainTableHeaderRowPosition = $currentBuildRow;
        $this->mainTableDataStartRow = $currentBuildRow + 1;
        $this->exportDataArray[]          = [
            'No',
            'No. Invoice',
            'Tgl Buat',
            'Pelanggan',
            'ID Pelanggan',
            'Paket',
            'Periode Mulai',
            'Periode Selesai',
            'Jumlah (Rp)',
            'Status Bayar',
            'Tgl Bayar',
            'Metode Bayar',
        ];

        // Data Rows
        if ($payments instanceof Collection ? $payments->isNotEmpty() : count($payments) > 0) {
            $no = 1;
            foreach ($payments as $payment) {
                $this->exportDataArray[] = [
                    $no++,
                    $payment->nomor_invoice,
                    $payment->created_at ? Carbon::parse($payment->created_at)->format('d/m/Y H:i') : '-',
                    $payment->customer->nama_customer ?? '-',
                    $payment->customer->id_customer ?? '-',
                    $payment->paket->kecepatan_paket ?? '-',
                    $payment->periode_tagihan_mulai ? Carbon::parse($payment->periode_tagihan_mulai)->format('d/m/Y') : '-',
                    $payment->periode_tagihan_selesai ? Carbon::parse($payment->periode_tagihan_selesai)->format('d/m/Y') : '-',
                    $payment->jumlah_tagihan,
                    $this->getStatusLabel($payment->status_pembayaran),
                    $payment->tanggal_pembayaran ? Carbon::parse($payment->tanggal_pembayaran)->format('d/m/Y') : '-',
                    $payment->metode_pembayaran ? Str::title($payment->metode_pembayaran) : '-',
                ];
            }
        }
    }

    protected function getStatusLabel($status): string
    {
        $labels = [
            'paid'                 => 'Lunas',
            'unpaid'               => 'Belum Bayar',
            'pending_confirmation' => 'Menunggu Konfirmasi',
            'cancelled'            => 'Dibatalkan',
            'failed'               => 'Gagal',
        ];
        return $labels[$status] ?? Str::title(str_replace('_', ' ', $status));
    }

    public function array(): array
    {
        return $this->exportDataArray;
    }

    public function styles(Worksheet $sheet)
    {
        // Title Styling - Changed from A1:L1 to B1:L1
        $sheet->getStyle('B1:L1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'fill' => [
                'fillType' => PhpSpreadsheetFill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFD9']
            ]
        ]);

        // Main Summary Styling - Changed from A4 to B4
        $sheet->getStyle('B4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => PhpSpreadsheetFill::FILL_SOLID, 'startColor' => ['rgb' => '366092']],
            'font' => ['color' => ['rgb' => 'FFFFFF']]
        ]);

        // Status Summary Styling - Changed from A8 to B8
        $sheet->getStyle('B8')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => PhpSpreadsheetFill::FILL_SOLID, 'startColor' => ['rgb' => '366092']],
            'font' => ['color' => ['rgb' => 'FFFFFF']]
        ]);

        // Status Summary Headers - Changed from A9:C9 to B9:D9
        $sheet->getStyle('B9:D9')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => PhpSpreadsheetFill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']]
        ]);

        // Set column A to be very narrow
        $sheet->getColumnDimension('A')->setWidth(3);

        // Add borders to summary sections - Updated ranges
        $sheet->getStyle('B5:C6')->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => PhpSpreadsheetBorder::BORDER_THIN]
            ]
        ]);

        $sheet->getStyle('B9:D14')->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => PhpSpreadsheetBorder::BORDER_THIN]
            ]
        ]);

        // Format currency columns
        $rupiahFormat = '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"??_);_(@_)';
        $sheet->getStyle('I:I')->getNumberFormat()->setFormatCode($rupiahFormat);
        $sheet->getStyle('C6')->getNumberFormat()->setFormatCode($rupiahFormat);

        // Set specific column widths instead of auto-size
        $sheet->getColumnDimension('B')->setWidth(15);  // No. Invoice
        $sheet->getColumnDimension('C')->setWidth(15);  // Tgl Buat
        $sheet->getColumnDimension('D')->setWidth(30);  // Pelanggan
        $sheet->getColumnDimension('E')->setWidth(12);  // ID Pelanggan
        $sheet->getColumnDimension('F')->setWidth(12);  // Paket
        $sheet->getColumnDimension('G')->setWidth(12);  // Periode Mulai
        $sheet->getColumnDimension('H')->setWidth(12);  // Periode Selesai
        $sheet->getColumnDimension('I')->setWidth(15);  // Jumlah (Rp)
        $sheet->getColumnDimension('J')->setWidth(15);  // Status Bayar
        $sheet->getColumnDimension('K')->setWidth(12);  // Tgl Bayar
        $sheet->getColumnDimension('L')->setWidth(15);  // Metode Bayar

        // Center align specific columns
        $sheet->getStyle('A' . $this->mainTableDataStartRow . ':A' . $sheet->getHighestRow())->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $this->mainTableDataStartRow . ':B' . $sheet->getHighestRow())->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $this->mainTableDataStartRow . ':C' . $sheet->getHighestRow())->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Add borders to summary sections
        $sheet->getStyle('B5:C6')->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => PhpSpreadsheetBorder::BORDER_THIN],
            ],
        ]);

        $sheet->getStyle('B9:D14')->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => PhpSpreadsheetBorder::BORDER_THIN],
            ],
        ]);

        return [
            // Customize specific rows
            $this->mainTableHeaderRowPosition => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => PhpSpreadsheetFill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Semua Tagihan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(2)->setRowHeight(14);
                $sheet->getRowDimension($this->summaryTitleRowPosition)->setRowHeight(18);
                $sheet->getRowDimension($this->summaryHeaderRowPosition)->setRowHeight(16);
                $sheet->getRowDimension($this->mainTableHeaderRowPosition)->setRowHeight(18);
            },
        ];
    }
}
