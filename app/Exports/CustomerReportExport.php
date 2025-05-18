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

class CustomerReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
            'ID Pelanggan',
            'Nama Pelanggan',
            'Status Pelanggan',
            'Paket',
            'Tgl Aktivasi',
            'Layanan Habis Terakhir (Visual)',
            'Layanan Habis Sebenarnya',
        ];

        // Sub-header untuk setiap bulan
        $monthSubHeaders = [];
        foreach ($this->displayedMonths as $monthName) {
            $mainHeaders[] = $monthName; // Header utama bulan (akan di-merge 3 kolom)
            $mainHeaders[] = '';         // Kolom kosong untuk merge
            $mainHeaders[] = '';         // Kolom kosong untuk merge

            $monthSubHeaders[] = 'Tgl Bayar';
            $monthSubHeaders[] = 'Status';
            $monthSubHeaders[] = 'No. Invoice';
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
        // Style untuk Judul Utama (Baris 1)
        $sheet->mergeCells('A1:' . $sheet->getHighestColumn() . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_CENTER);

        // Style untuk Periode (Baris 2)
        $sheet->mergeCells('A2:' . $sheet->getHighestColumn() . '2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_CENTER);

        // Style untuk Header Utama Tabel (Baris 4)
        $sheet->getStyle('A4:' . $sheet->getHighestColumn() . '4')->getFont()->setBold(true);
        $sheet->getStyle('A4:' . $sheet->getHighestColumn() . '4')->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_CENTER)->setVertical(PhpSpreadsheetAlignment::VERTICAL_CENTER);
        $sheet->getStyle('A4:' . $sheet->getHighestColumn() . '4')->getFill()->setFillType(PhpSpreadsheetFill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0'); // Warna abu-abu muda

        // Style untuk Sub-Header Bulan (Baris 5)
        $sheet->getStyle('A5:' . $sheet->getHighestColumn() . '5')->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle('A5:' . $sheet->getHighestColumn() . '5')->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_CENTER)->setVertical(PhpSpreadsheetAlignment::VERTICAL_CENTER);
        $sheet->getStyle('A5:' . $sheet->getHighestColumn() . '5')->getFill()->setFillType(PhpSpreadsheetFill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');

                               // Merge header kolom bulan di Baris 4
        $startColumnIndex = 7; // Kolom setelah 'Layanan Habis Sebenarnya' (G), jadi mulai dari H (indeks 8)
        foreach ($this->displayedMonths as $monthName) {
            $startLetter = PhpSpreadsheetCoordinate::stringFromColumnIndex($startColumnIndex + 1); // +1 karena index PhpSpreadsheet mulai dari 1
            $endLetter   = PhpSpreadsheetCoordinate::stringFromColumnIndex($startColumnIndex + 3);
            $sheet->mergeCells($startLetter . '4:' . $endLetter . '4');
            $startColumnIndex += 3;
        }

        // Alignment tengah untuk data bulanan
        $startColumnForMonthsData = 8;
        if (! empty($this->displayedMonths)) {
            $lastMonthDataColumnIndex = $startColumnForMonthsData + (count($this->displayedMonths) * 3) - 1;
            if ($lastMonthDataColumnIndex >= $startColumnForMonthsData) {
                $startLetter = PhpSpreadsheetCoordinate::stringFromColumnIndex($startColumnForMonthsData);
                $endLetter   = PhpSpreadsheetCoordinate::stringFromColumnIndex($lastMonthDataColumnIndex);
                $sheet->getStyle($startLetter . (5 + 1) . ':' . $endLetter . $sheet->getHighestRow()) // Mulai dari baris setelah sub-header
                    ->getAlignment()->setHorizontal(PhpSpreadsheetAlignment::HORIZONTAL_CENTER);
            }
        }

        // Border untuk seluruh tabel data (mulai dari header utama tabel)
        $lastRow    = $sheet->getHighestRow();
        $lastCol    = $sheet->getHighestColumn();
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => PhpSpreadsheetBorder::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle('A4:' . $lastCol . $lastRow)->applyFromArray($styleArray);

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
                $event->sheet->getRowDimension(1)->setRowHeight(25); // Judul Utama
                $event->sheet->getRowDimension(2)->setRowHeight(20); // Periode
                $event->sheet->getRowDimension(4)->setRowHeight(30); // Header Utama Tabel
                $event->sheet->getRowDimension(5)->setRowHeight(20); // Sub-Header Bulan
            },
        ];
    }
}
