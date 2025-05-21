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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate as PhpSpreadsheetCoordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment as PhpSpreadsheetAlignment;
use PhpOffice\PhpSpreadsheet\Style\Border as PhpSpreadsheetBorder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as PhpSpreadsheetNumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllInvoicesReportExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    protected array $exportDataArray;
    protected string $pageTitle;
    protected array $filterInfo;
    protected int $summaryDataRowCount = 0;
    protected int $summaryHeaderRowPosition;
    protected int $mainTableHeaderRowPosition;
    protected int $summaryTitleRowPosition;
    protected int $mainTableDataStartRow;

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
        $this->exportDataArray     = [];
        $this->summaryDataRowCount = 0;
        $currentBuildRow           = 1;

        // Baris 1: Judul Utama Laporan
        $this->exportDataArray[] = [$this->pageTitle];
        $currentBuildRow++;
        // Baris 2: Info Filter
        $filterText    = "Filter Aktif: ";
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

        $this->exportDataArray[] = [! empty($activeFilters) ? $filterText . implode(', ', $activeFilters) : $filterText . "Tidak ada filter"];
        $currentBuildRow++;
                                                 // Baris 3: Baris kosong
        $this->exportDataArray[] = ["", "", ""]; // Beri 3 elemen agar konsisten untuk merge C jika perlu
        $currentBuildRow++;

        // Baris 4: Judul Ringkasan
        $this->summaryTitleRowPosition = $currentBuildRow;
        $this->exportDataArray[]       = ["RINGKASAN LAPORAN (BERDASARKAN FILTER)", "", ""]; // Kolom B & C kosong untuk merge
        $currentBuildRow++;
        // Baris 5: Header untuk Ringkasan
        $this->summaryHeaderRowPosition = $currentBuildRow;
        $this->exportDataArray[]        = ['Deskripsi', 'Jumlah Invoice', 'Total Nilai (Rp)'];
        $currentBuildRow++;
        // Baris 6: Data Ringkasan Total
        $this->exportDataArray[] = ["Total Semua Tagihan Dibuat", $totalInvoices, $totalAmountAll];
        $this->summaryDataRowCount++;
        // Baris 7 dst: Data Ringkasan per Status
        foreach ($paymentStatusesForFilter as $statusKey => $statusLabel) {
            if (isset($summaryByStatus[$statusKey])) {
                $this->exportDataArray[] = ["Tagihan " . $statusLabel, $summaryByStatus[$statusKey]->count, $summaryByStatus[$statusKey]->total_amount];
            } else {
                $this->exportDataArray[] = ["Tagihan " . $statusLabel, 0, 0];
            }
            $this->summaryDataRowCount++;
        }
        // Baris kosong setelah summary
        $this->exportDataArray[] = ["", "", ""];
        $currentBuildRow += $this->summaryDataRowCount + 1;

        // Header Tabel Utama
        $this->mainTableHeaderRowPosition = $currentBuildRow;
        $this->exportDataArray[]          = [
            'No. Invoice', 'Tgl Buat', 'Pelanggan', 'ID Pelanggan', 'Paket',
            'Periode Mulai', 'Periode Selesai', 'Jumlah (Rp)', 'Status Bayar',
            'Tgl Bayar', 'Metode Bayar', 'Catatan Admin',
        ];
        $this->mainTableDataStartRow = $this->mainTableHeaderRowPosition + 1;

        if ($payments instanceof \Illuminate\Support\Collection  ? $payments->isNotEmpty() : count($payments) > 0) {
            foreach ($payments as $payment) {
                $this->exportDataArray[] = [
                    $payment->nomor_invoice,
                    $payment->created_at ? Carbon::parse($payment->created_at)->format('d/m/Y H:i') : '-',
                    $payment->customer->nama_customer ?? '-',
                    $payment->customer->id_customer ?? '-',
                    $payment->paket->kecepatan_paket ?? '-',
                    $payment->periode_tagihan_mulai ? Carbon::parse($payment->periode_tagihan_mulai)->format('d/m/Y') : '-',
                    $payment->periode_tagihan_selesai ? Carbon::parse($payment->periode_tagihan_selesai)->format('d/m/Y') : '-',
                    $payment->jumlah_tagihan,
                    Str::title(str_replace('_', ' ', $payment->status_pembayaran)),
                    $payment->tanggal_pembayaran ? Carbon::parse($payment->tanggal_pembayaran)->format('d/m/Y') : '-',
                    $payment->metode_pembayaran ? Str::title($payment->metode_pembayaran) : '-',
                    $payment->catatan_admin ?? '-',
                ];
            }
        } else {
            $this->exportDataArray[] = ['Tidak ada data tagihan yang cocok dengan filter Anda.', null, null, null, null, null, null, null, null, null, null, null];
        }
    }

    public function array(): array
    {
        return $this->exportDataArray;
    }

    public function styles(Worksheet $sheet)
    {
        $rupiahFormat            = '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"??_);_(@_)';
        $mainTableColumnCount    = 12;
        $lastColumnLetterOverall = PhpSpreadsheetCoordinate::stringFromColumnIndex($mainTableColumnCount);

        // Style Umum
        $defaultFont = ['name' => 'Arial', 'size' => 9];
        $sheet->getParent()->getDefaultStyle()->getFont()->applyFromArray($defaultFont);

        // Judul Utama Laporan (Baris 1)
        $sheet->mergeCells('A1:' . $lastColumnLetterOverall . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setUnderline(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_CENTER)->setVertical(PhpSpreadsheetAlignment::VERTICAL_CENTER);

        // Info Filter (Baris 2)
        $sheet->mergeCells('A2:' . $lastColumnLetterOverall . '2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(8);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_LEFT)->setVertical(PhpSpreadsheetAlignment::VERTICAL_CENTER);

        // Baris 3 adalah baris kosong

        // Judul Ringkasan (Baris 4, sesuai $this->summaryTitleRowPosition)
        $sheet->mergeCells('A' . $this->summaryTitleRowPosition . ':C' . $this->summaryTitleRowPosition);
        $sheet->getStyle('A' . $this->summaryTitleRowPosition)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A' . $this->summaryTitleRowPosition)->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_CENTER)->setVertical(PhpSpreadsheetAlignment::VERTICAL_CENTER);

        // Header Tabel Ringkasan (Baris 5, sesuai $this->summaryHeaderRowPosition)
        $sheet->getStyle('A' . $this->summaryHeaderRowPosition . ':C' . $this->summaryHeaderRowPosition)->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle('A' . $this->summaryHeaderRowPosition . ':C' . $this->summaryHeaderRowPosition)->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_CENTER)->setVertical(PhpSpreadsheetAlignment::VERTICAL_CENTER);

        // Data Ringkasan
        $summaryDataStartRow = $this->summaryHeaderRowPosition + 1;
        $summaryDataEndRow   = $summaryDataStartRow + $this->summaryDataRowCount - 1;
        $sheet->getStyle('B' . $summaryDataStartRow . ':B' . $summaryDataEndRow)->getNumberFormat()->setFormatCode(PhpSpreadsheetNumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->getStyle('C' . $summaryDataStartRow . ':C' . $summaryDataEndRow)->getNumberFormat()->setFormatCode($rupiahFormat);
        $sheet->getStyle('B' . $summaryDataStartRow . ':C' . $summaryDataEndRow)->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A' . $summaryDataStartRow . ':A' . $summaryDataEndRow)->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_LEFT);

        // Header Tabel Utama (sesuai $this->mainTableHeaderRowPosition)
        $sheet->getStyle('A' . $this->mainTableHeaderRowPosition . ':' . $lastColumnLetterOverall . $this->mainTableHeaderRowPosition)->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle('A' . $this->mainTableHeaderRowPosition . ':' . $lastColumnLetterOverall . $this->mainTableHeaderRowPosition)->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_CENTER)->setVertical(PhpSpreadsheetAlignment::VERTICAL_CENTER);

        // Format kolom Jumlah (Rp) di tabel utama (Kolom H)
        $sheet->getStyle('H' . $this->mainTableDataStartRow . ':H' . $sheet->getHighestRow())->getNumberFormat()->setFormatCode($rupiahFormat);
        $sheet->getStyle('H' . $this->mainTableDataStartRow . ':H' . $sheet->getHighestRow())->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_RIGHT);

        // Alignment tengah untuk Status Bayar (Kolom I)
        $sheet->getStyle('I' . $this->mainTableDataStartRow . ':I' . $sheet->getHighestRow())->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_CENTER);

        // Border untuk tabel ringkasan dan tabel utama
        $styleArrayBorders = ['borders' => ['allBorders' => ['borderStyle' => PhpSpreadsheetBorder::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
        if ($summaryDataEndRow >= $this->summaryHeaderRowPosition) {
            $sheet->getStyle('A' . $this->summaryHeaderRowPosition . ':C' . $summaryDataEndRow)->applyFromArray($styleArrayBorders);
        }
        if ($sheet->getHighestRow() >= $this->mainTableHeaderRowPosition) {
            $sheet->getStyle('A' . $this->mainTableHeaderRowPosition . ':' . $lastColumnLetterOverall . $sheet->getHighestRow())->applyFromArray($styleArrayBorders);
        }
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
