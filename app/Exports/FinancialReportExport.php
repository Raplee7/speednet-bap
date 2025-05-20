<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment as PhpSpreadsheetAlignment;
use PhpOffice\PhpSpreadsheet\Style\Border as PhpSpreadsheetBorder;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill as PhpSpreadsheetFill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportExport implements FromArray, WithColumnWidths, WithStyles, WithTitle, WithEvents
{
    protected array $exportDataArray;
    protected string $reportPeriodLabel;
    protected string $pageTitle;
    protected float $totalIncome;
    protected Collection $incomeByPaket;
    protected Collection $incomeByMethod;

    public function __construct(array $dataFromController)
    {
        $this->pageTitle         = $dataFromController['pageTitle'] ?? 'Laporan Keuangan Pendapatan';
        $this->reportPeriodLabel = $dataFromController['reportPeriodLabel'] ?? 'Tidak Diketahui';
        $this->totalIncome       = $dataFromController['totalIncome'] ?? 0;
        $this->incomeByPaket     = collect($dataFromController['incomeByPaket'] ?? []);
        $this->incomeByMethod    = collect($dataFromController['incomeByMethod'] ?? []);

        $this->prepareExportDataArray();
    }

    protected function prepareExportDataArray(): void
    {
        $this->exportDataArray = [];

                                                               // Baris 1: Judul Utama Laporan
        $this->exportDataArray[] = [$this->pageTitle, '', '']; // Kolom A diisi, B & C KOSONG untuk merge
                                                               // Baris 2: Periode Laporan
        $this->exportDataArray[] = ['Periode: ' . $this->reportPeriodLabel, '', ''];
        // Baris 3: Total Pendapatan Keseluruhan
        $this->exportDataArray[] = ['Total Pendapatan Keseluruhan:', $this->totalIncome, ''];
        // Baris 4: Baris kosong sebagai pemisah
        $this->exportDataArray[] = ['', '', ''];

                                                                             // Section: Rincian Pendapatan per Paket
        $this->exportDataArray[] = ['Rincian Pendapatan per Paket', '', '']; // Judul Section (Baris 5)
                                                                             // Header Tabel untuk Paket (Baris 6) - Ini yang akan terlihat
        $this->exportDataArray[] = ['Deskripsi Paket (Kecepatan)', 'Jumlah Transaksi', 'Total Pendapatan (Rp)'];
        if ($this->incomeByPaket->isNotEmpty()) {
            foreach ($this->incomeByPaket as $item) {
                $this->exportDataArray[] = [
                    $item->kecepatan_paket,
                    $item->transaction_count,
                    $item->total,
                ];
            }
        } else {
            $this->exportDataArray[] = ['Tidak ada data pendapatan per paket.', '', ''];
        }
        $this->exportDataArray[] = ['', '', '']; // Baris kosong

                                                                                         // Section: Rincian Pendapatan per Metode Pembayaran
        $this->exportDataArray[] = ['Rincian Pendapatan per Metode Pembayaran', '', '']; // Judul Section
                                                                                         // Header Tabel untuk Metode Pembayaran
        $this->exportDataArray[] = ['Metode Pembayaran', 'Jumlah Transaksi', 'Total Pendapatan (Rp)'];
        if ($this->incomeByMethod->isNotEmpty()) {
            foreach ($this->incomeByMethod as $metode => $methodData) {
                $this->exportDataArray[] = [
                    Str::title($metode),
                    $methodData['count'],
                    $methodData['total'],
                ];
            }
        } else {
            $this->exportDataArray[] = ['Tidak ada data pendapatan per metode pembayaran.', '', ''];
        }
    }

    public function array(): array
    {
        return $this->exportDataArray;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45, // Kolom deskripsi
            'B' => 25, // Kolom jumlah transaksi
            'C' => 35, // Kolom total pendapatan
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Format currency
        $rupiahFormat = '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"??_);_(@_)';

        // Base style untuk seluruh worksheet
        $sheet->getParent()->getDefaultStyle()->applyFromArray([
            'font'      => [
                'name' => 'Calibri',
                'size' => 11,
            ],
            'alignment' => [
                'vertical' => PhpSpreadsheetAlignment::VERTICAL_CENTER,
            ],
        ]);

        // Judul Utama (Row 1)
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => [
                'bold'  => true,
                'size'  => 18,
                'color' => ['rgb' => '1F497D'],
            ],
            'alignment' => [
                'horizontal' => PhpSpreadsheetAlignment::HORIZONTAL_CENTER,
                'vertical'   => PhpSpreadsheetAlignment::VERTICAL_CENTER,
            ],
            'fill'      => [
                'fillType'   => PhpSpreadsheetFill::FILL_GRADIENT_LINEAR,
                'startColor' => ['rgb' => 'DAE3F3'],
                'endColor'   => ['rgb' => 'FFFFFF'],
                'rotation'   => 90,
            ],
            'borders'   => [
                'bottom' => [
                    'borderStyle' => PhpSpreadsheetBorder::BORDER_MEDIUM,
                    'color'       => ['rgb' => '1F497D'],
                ],
            ],
        ]);

        // Periode Laporan (Row 2)
        $sheet->mergeCells('A2:C2');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => [
                'italic' => true,
                'size'   => 12,
                'color'  => ['rgb' => '1F497D'],
            ],
            'alignment' => [
                'horizontal' => PhpSpreadsheetAlignment::HORIZONTAL_CENTER,
            ],
            'fill'      => [
                'fillType'   => PhpSpreadsheetFill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA'],
            ],
        ]);

        // Total Pendapatan (Row 3)
        $sheet->getStyle('A3:C3')->applyFromArray([
            'font'    => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'    => [
                'fillType'   => PhpSpreadsheetFill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFD9'],
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => PhpSpreadsheetBorder::BORDER_MEDIUM,
                    'color'       => ['rgb' => '70AD47'],
                ],
            ],
        ]);

        // Style untuk header tabel
        $headerStyle = [
            'font'      => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill'      => [
                'fillType'   => PhpSpreadsheetFill::FILL_SOLID,
                'startColor' => ['rgb' => '2F5597'],
            ],
            'alignment' => [
                'horizontal' => PhpSpreadsheetAlignment::HORIZONTAL_CENTER,
                'vertical'   => PhpSpreadsheetAlignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => PhpSpreadsheetBorder::BORDER_THIN,
                    'color'       => ['rgb' => '2F5597'],
                ],
                'outline'    => [
                    'borderStyle' => PhpSpreadsheetBorder::BORDER_MEDIUM,
                ],
            ],
        ];

        // Style untuk section titles
        $sectionStyle = [
            'font'      => [
                'bold'  => true,
                'size'  => 12,
                'color' => ['rgb' => '2F5597'],
            ],
            'fill'      => [
                'fillType'   => PhpSpreadsheetFill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
            'alignment' => [
                'horizontal' => PhpSpreadsheetAlignment::HORIZONTAL_LEFT,
                'indent'     => 1,
            ],
            'borders'   => [
                'outline' => [
                    'borderStyle' => PhpSpreadsheetBorder::BORDER_MEDIUM,
                    'color'       => ['rgb' => '2F5597'],
                ],
            ],
        ];

        // Style untuk data cells
        $dataStyle = [
            'alignment' => [
                'vertical' => PhpSpreadsheetAlignment::VERTICAL_CENTER,
            ],
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => PhpSpreadsheetBorder::BORDER_THIN,
                    'color'       => ['rgb' => 'B4C6E7'],
                ],
            ],
        ];

        // Apply styles
        $this->applyTableStyles($sheet, $headerStyle, $sectionStyle, $dataStyle, $rupiahFormat);

        return [];
    }

    protected function applyTableStyles(Worksheet $sheet, array $headerStyle, array $sectionStyle, array $dataStyle, string $rupiahFormat): void
    {
        $currentRow = 5;

        // Section Paket
        $this->applySectionStyles($sheet, $currentRow, $headerStyle, $sectionStyle, $dataStyle, $rupiahFormat, $this->incomeByPaket->count());

        // Section Metode Pembayaran
        $currentRow = 6 + $this->incomeByPaket->count() + 2;
        $this->applySectionStyles($sheet, $currentRow, $headerStyle, $sectionStyle, $dataStyle, $rupiahFormat, $this->incomeByMethod->count());

        // Format numbers
        $sheet->getStyle('B3')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('C3')->getNumberFormat()->setFormatCode($rupiahFormat);

        // Set alignment untuk kolom angka
        $sheet->getStyle('B:C')->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_RIGHT);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                                                              // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(40); // Title
                $sheet->getRowDimension(2)->setRowHeight(30); // Period
                $sheet->getRowDimension(3)->setRowHeight(35); // Total Income

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(50);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(35);

                // Freeze panes
                $sheet->freezePane('A7');

                // Set zoom level
                $sheet->getSheetView()->setZoomScale(100);

                // Add print settings
                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
            },
        ];
    }

    protected function applySectionStyles(
        Worksheet $sheet,
        int $startRow,
        array $headerStyle,
        array $sectionStyle,
        array $dataStyle,
        string $rupiahFormat,
        int $dataCount
    ): void {
        // Section title
        $sheet->mergeCells("A{$startRow}:C{$startRow}");
        $sheet->getStyle("A{$startRow}")->applyFromArray($sectionStyle);

        // Headers
        $headerRow = $startRow + 1;
        $sheet->getStyle("A{$headerRow}:C{$headerRow}")->applyFromArray($headerStyle);

        // Data rows
        if ($dataCount > 0) {
            $dataStartRow = $headerRow + 1;
            $dataEndRow   = $dataStartRow + $dataCount - 1;

            // Apply data styles
            $sheet->getStyle("A{$dataStartRow}:C{$dataEndRow}")->applyFromArray($dataStyle);

            // Format numbers
            $sheet->getStyle("B{$dataStartRow}:B{$dataEndRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');

            $sheet->getStyle("C{$dataStartRow}:C{$dataEndRow}")
                ->getNumberFormat()
                ->setFormatCode($rupiahFormat);

            // Zebra striping
            for ($row = $dataStartRow; $row <= $dataEndRow; $row += 2) {
                $sheet->getStyle("A{$row}:C{$row}")->getFill()
                    ->setFillType(PhpSpreadsheetFill::FILL_SOLID)
                    ->setStartColor(new Color('F5F8FF'));
            }
        }
    }

    public function title(): string
    {
        return 'Laporan Pendapatan';
    }
}
