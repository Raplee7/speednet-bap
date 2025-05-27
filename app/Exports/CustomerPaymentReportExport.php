<?php
namespace App\Exports;

// Facades dan Class yang dibutuhkan oleh Maatwebsite/Excel
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Untuk event setelah sheet dibuat
use Maatwebsite\Excel\Concerns\WithEvents;     // Untuk mendaftarkan event

// Kelas-kelas dari PhpOffice\PhpSpreadsheet yang kita gunakan
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate as PhpSpreadsheetCoordinate;

// Model dan Class lain yang Anda gunakan
use PhpOffice\PhpSpreadsheet\Style\Alignment as PhpSpreadsheetAlignment;
use PhpOffice\PhpSpreadsheet\Style\Border as PhpSpreadsheetBorder;
use PhpOffice\PhpSpreadsheet\Style\Fill as PhpSpreadsheetFill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerPaymentReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected Collection $reportData;
    protected array $displayedMonths;
    protected int $selectedYear;
    protected array $allMonthNames;
    protected int $selectedStartMonth;
    protected int $selectedEndMonth;

    public function __construct(array $data)
    {
        $this->reportData         = $data['reportData'] ?? collect();
        $this->displayedMonths    = $data['displayedMonths'] ?? [];
        $this->selectedYear       = $data['selectedYear'] ?? Carbon::now()->year; // Ambil dari data
        $this->allMonthNames      = $data['allMonthNames'] ?? [];                 // Ambil dari data
        $this->selectedStartMonth = $data['selectedStartMonth'] ?? 1;             // Ambil dari data
        $this->selectedEndMonth   = $data['selectedEndMonth'] ?? 12;              // Ambil dari data
    }

    public function collection(): Collection
    {
        return $this->reportData;
    }

    public function headings(): array
    {
        // Baris Judul Laporan Utama (akan di-merge)
        $mainTitle = [
            'LAPORAN PEMBAYARAN PELANGGAN', // Sel A1
                                            // Sel B1 sampai kolom terakhir sebelum bulan akan dikosongkan untuk merge
        ];
        for ($i = 1; $i < 7 + (count($this->displayedMonths) * 3) - 1; $i++) { // -1 karena A1 sudah diisi
            $mainTitle[] = '';
        }

        // Baris Periode Laporan (akan di-merge)
        $periodTitle = [
            'Periode: ' . ($this->allMonthNames[$this->selectedStartMonth] ?? '') . ' - ' . ($this->allMonthNames[$this->selectedEndMonth] ?? '') . ' ' . $this->selectedYear,
        ];
        for ($i = 1; $i < 7 + (count($this->displayedMonths) * 3) - 1; $i++) {
            $periodTitle[] = '';
        }

        // Header tabel utama
        $mainHeaders = [
            'ID',
            'Nama',
            'Status',
            'Paket',
            'Tgl Aktivasi',
            'Layanan Habis',
            'Layanan Actual',
        ];

        // Sub-header untuk setiap bulan
        $monthSubHeaders = [];
        foreach ($this->displayedMonths as $monthName) {
            $mainHeaders[] = $monthName; // Header utama bulan (akan di-merge 3 kolom)
            $mainHeaders[] = '';         // Kolom kosong untuk merge
            $mainHeaders[] = '';         // Kolom kosong untuk merge

            $monthSubHeaders[] = 'Tgl';
            $monthSubHeaders[] = 'Status';
            $monthSubHeaders[] = 'Invoice';
        }

        // Gabungkan semua header
        return [
            $mainTitle,                                          // Baris 1: Judul Utama
            $periodTitle,                                        // Baris 2: Periode
            [],                                                  // Baris 3: Baris kosong sebagai pemisah
            $mainHeaders,                                        // Baris 4: Header utama tabel (termasuk nama bulan)
            array_merge(array_fill(0, 7, ''), $monthSubHeaders), // Baris 5: Sub-header untuk detail bulan
        ];
    }

    public function map($row): array
    {
        $customer  = $row['customer'] ?? null;
        $mappedRow = [
            $customer ? $customer->id_customer : '-',
            $customer ? $customer->nama_customer : '-',
            $customer ? Str::title(str_replace('_', ' ', $customer->status)) : '-',
            strip_tags($row['paket_info'] ?? '-'),
            $row['tgl_aktivasi'] ?? '-',
            $row['tgl_layanan_habis_terakhir_visual'] ?? '-',
            $row['tgl_layanan_habis_sebenarnya'] ?? '-',
        ];

        foreach ($this->displayedMonths as $monthName) {
            $monthData   = $row['monthly_status'][$monthName] ?? ['tgl_bayar' => '-', 'text' => '-', 'invoice_no' => '-'];
            $mappedRow[] = $monthData['tgl_bayar'] ?? '-';
            $mappedRow[] = $monthData['text'] ?? '-';
            $mappedRow[] = $monthData['invoice_no'] ?? '-';
        }
        return $mappedRow;
    }

    public function styles(Worksheet $sheet)
    {
        // Default styles
        $sheet->getDefaultRowDimension()->setRowHeight(18);
        
        // Title Styling (Row 1)
        $sheet->mergeCells('A1:' . $sheet->getHighestColumn() . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => '000000']
            ],
            'alignment' => [
                'horizontal' => PhpSpreadsheetAlignment::HORIZONTAL_CENTER,
                'vertical' => PhpSpreadsheetAlignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => PhpSpreadsheetFill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFD9']
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => PhpSpreadsheetBorder::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Period Info (Row 2)
        $sheet->mergeCells('A2:' . $sheet->getHighestColumn() . '2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11
            ],
            'alignment' => [
                'horizontal' => PhpSpreadsheetAlignment::HORIZONTAL_CENTER
            ],
            'fill' => [
                'fillType' => PhpSpreadsheetFill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA']
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => PhpSpreadsheetBorder::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Headers Style (Row 4)
        $mainHeaderStyle = [
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'alignment' => [
                'horizontal' => PhpSpreadsheetAlignment::HORIZONTAL_CENTER,
                'vertical' => PhpSpreadsheetAlignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'fill' => [
                'fillType' => PhpSpreadsheetFill::FILL_SOLID,
                'startColor' => ['rgb' => '366092']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => PhpSpreadsheetBorder::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ],
                'outline' => [
                    'borderStyle' => PhpSpreadsheetBorder::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A4:' . $sheet->getHighestColumn() . '4')->applyFromArray($mainHeaderStyle);

        // Sub Headers Style (Row 5)
        $subHeaderStyle = [
            'font' => [
                'bold' => true,
                'size' => 9
            ],
            'alignment' => [
                'horizontal' => PhpSpreadsheetAlignment::HORIZONTAL_CENTER,
                'vertical' => PhpSpreadsheetAlignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'fill' => [
                'fillType' => PhpSpreadsheetFill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => PhpSpreadsheetBorder::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A5:' . $sheet->getHighestColumn() . '5')->applyFromArray($subHeaderStyle);

        // Set Column Widths
        $sheet->getColumnDimension('A')->setWidth(8);   // ID
        $sheet->getColumnDimension('B')->setWidth(25);  // Nama
        $sheet->getColumnDimension('C')->setWidth(12);  // Status
        $sheet->getColumnDimension('D')->setWidth(12);  // Paket
        $sheet->getColumnDimension('E')->setWidth(12);  // Tgl Aktivasi
        $sheet->getColumnDimension('F')->setWidth(12);  // Layanan Habis
        $sheet->getColumnDimension('G')->setWidth(12);  // Layanan Actual

        // Style for data cells
        $dataStyle = [
            'alignment' => [
                'vertical' => PhpSpreadsheetAlignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => PhpSpreadsheetBorder::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A6:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->applyFromArray($dataStyle);

        // Month columns styling with different colors
        $startColumnIndex = 7;
        $monthColors = [
            'E3F2FD', // Light Blue
            'F3E5F5', // Light Purple
            'E8F5E9', // Light Green
            'FFF3E0', // Light Orange
            'FFEBEE', // Light Red
            'F3E5F5', // Light Purple
            'E0F7FA', // Light Cyan
            'FFF8E1', // Light Amber
            'F1F8E9', // Light Light Green
            'FCE4EC', // Light Pink
            'E8EAF6', // Light Indigo
            'F9FBE7'  // Light Lime
        ];

        foreach ($this->displayedMonths as $index => $monthName) {
            $startLetter = PhpSpreadsheetCoordinate::stringFromColumnIndex($startColumnIndex + 1);
            $endLetter = PhpSpreadsheetCoordinate::stringFromColumnIndex($startColumnIndex + 3);
            
            // Set month column widths
            $sheet->getColumnDimension($startLetter)->setWidth(10);  // Tgl Bayar
            $sheet->getColumnDimension(PhpSpreadsheetCoordinate::stringFromColumnIndex($startColumnIndex + 2))->setWidth(12);  // Status
            $sheet->getColumnDimension($endLetter)->setWidth(13);  // Invoice
            
            // Merge month header cells
            $sheet->mergeCells($startLetter . '4:' . $endLetter . '4');
            
            // Apply month specific color to header and data cells
            $colorIndex = $index % count($monthColors);
            
            // Style for month header
            $sheet->getStyle($startLetter . '4:' . $endLetter . '4')->applyFromArray([
                'fill' => [
                    'fillType' => PhpSpreadsheetFill::FILL_SOLID,
                    'startColor' => ['rgb' => $monthColors[$colorIndex]]
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'] // Black text for better contrast
                ]
            ]);
            
            // Style for month sub-headers
            $sheet->getStyle($startLetter . '5:' . $endLetter . '5')->applyFromArray([
                'fill' => [
                    'fillType' => PhpSpreadsheetFill::FILL_SOLID,
                    'startColor' => ['rgb' => $monthColors[$colorIndex]]
                ],
                'font' => [
                    'bold' => true,
                    'size' => 8
                ]
            ]);
            
            // Apply color to ALL data cells (not just even rows)
            $colorIndex = $index % count($monthColors);
            for ($row = 6; $row <= $sheet->getHighestRow(); $row++) {
                $sheet->getStyle($startLetter . $row . ':' . $endLetter . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => PhpSpreadsheetFill::FILL_SOLID,
                        'startColor' => ['rgb' => $monthColors[$colorIndex]]
                    ]
                ]);
            }
            
            $startColumnIndex += 3;
        }

        // Freeze panes
        $sheet->freezePane('A6');

        return [];
    }

    /**
     * Mendaftarkan event.
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            // Event untuk mengatur tinggi baris header
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getRowDimension(1)->setRowHeight(30);  // Title
                $event->sheet->getRowDimension(2)->setRowHeight(20);  // Period
                $event->sheet->getRowDimension(3)->setRowHeight(8);   // Spacing
                $event->sheet->getRowDimension(4)->setRowHeight(25);  // Main Headers
                $event->sheet->getRowDimension(5)->setRowHeight(20);  // Sub Headers
                
                // Set data rows height
                for ($row = 6; $row <= $event->sheet->getHighestRow(); $row++) {
                    $event->sheet->getRowDimension($row)->setRowHeight(18);
                }

                // Protection
                $event->sheet->getProtection()->setSheet(true);
                $event->sheet->getProtection()->setSort(true);
                $event->sheet->getProtection()->setInsertRows(true);
                $event->sheet->getProtection()->setFormatCells(true);
            },
        ];
    }
}
