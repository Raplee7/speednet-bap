<?php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerProfileReportExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    protected array $exportDataArray;
    protected string $pageTitle;
    protected array $filterInfo;
    protected array $tableHeaders;
    protected int $titleRow       = 1;
    protected int $filterInfoRow  = 2;
    protected int $headerRowStart = 4;
    protected int $mainTableHeaderRowPosition;
    protected int $mainTableDataStartRow;

    public function __construct(array $dataFromController)
    {
        $this->pageTitle = $dataFromController['pageTitle'] ?? 'Laporan Data Pelanggan';

        // Perbaikan filter info
        $this->filterInfo = [
            'search_query'                 => $dataFromController['filterInfo']['search_query'] ?? null,
            'status_pelanggan'             => $dataFromController['filterInfo']['status_pelanggan'] ?? null,
            'paket_info'                   => $dataFromController['filterInfo']['paket_info'] ?? null,
            'activation_reportPeriodLabel' => $dataFromController['activation_reportPeriodLabel'] ?? null,
            'activation_periodType'        => $dataFromController['activation_periodType'] ?? null,
        ];

        $this->prepareTableHeaders();
        $this->prepareExportDataArray($dataFromController['customers'] ?? collect());
    }

    protected function prepareTableHeaders(): void
    {
        $this->tableHeaders = [
            'ID Pelanggan', 'Nama', 'NIK', 'Alamat', 'No. WA',
            'Path Foto KTP', 'Path Foto Rumah', 'Paket', 'User Aktif',
            'Model Perangkat', 'SN Perangkat', 'IP PPPoE', 'IP ONU',
            'Tgl Aktivasi', 'Status',
        ];
    }

    protected function prepareExportDataArray(iterable $customers): void
    {
        $this->exportDataArray = [];
        $currentBuildRow       = 1;

        // Baris 1: Judul Utama
        $this->exportDataArray[] = [$this->pageTitle];
        $currentBuildRow++;

        // Baris 2: Info Filter yang Diperbaiki
        $activeFilters = [];

        if (! empty($this->filterInfo['search_query'])) {
            $activeFilters[] = "Cari: " . $this->filterInfo['search_query'];
        }

        if (! empty($this->filterInfo['status_pelanggan'])) {
            $activeFilters[] = "Status: " . $this->filterInfo['status_pelanggan'];
        }

        if (! empty($this->filterInfo['paket_info'])) {
            $activeFilters[] = "Paket: " . $this->filterInfo['paket_info'];
        }

        // Filter periode aktivasi
        if (! empty($this->filterInfo['activation_reportPeriodLabel'])) {
            if ($this->filterInfo['activation_reportPeriodLabel'] !== 'Semua Periode' &&
                ! Str::contains($this->filterInfo['activation_reportPeriodLabel'], ['Tidak Valid', 'Tidak Lengkap', 'Error Filter'])) {
                $activeFilters[] = "Periode Aktivasi: " . $this->filterInfo['activation_reportPeriodLabel'];
            }
        }

        $filterText = ! empty($activeFilters) ?
        "Filter Aktif: " . implode('; ', $activeFilters) :
        "Filter Aktif: Tidak ada filter diterapkan";

        $this->exportDataArray[] = [$filterText];
        $currentBuildRow++;

        // Baris 3: Baris kosong
        $this->exportDataArray[] = [];
        $currentBuildRow++;
        $this->headerRowStart = $currentBuildRow - 1;

        $this->tableHeaders = [
            'ID Pelanggan', 'Nama', 'NIK', 'Alamat', 'No. WA',
            'Path Foto KTP', 'Path Foto Rumah', 'Paket', 'User Aktif',
            'Model Perangkat', 'SN Perangkat', 'IP PPPoE', 'IP ONU',
            'Tgl Aktivasi', 'Status',
        ];
        $this->exportDataArray[] = $this->tableHeaders;

        if ($customers->isNotEmpty()) {
            foreach ($customers as $customer) {
                $ktp_url   = $customer->foto_ktp_customer ? url('storage/' . $customer->foto_ktp_customer) : '-';
                $rumah_url = $customer->foto_timestamp_rumah ? url('storage/' . $customer->foto_timestamp_rumah) : '-';

                $this->exportDataArray[] = [
                    $customer->id_customer, $customer->nama_customer, $customer->nik_customer ?? '-',
                    $customer->alamat_customer ?? '-', $customer->wa_customer ? "'" . $customer->wa_customer : '-',
                    $ktp_url,   // Ganti path dengan URL
                    $rumah_url, // Ganti path dengan URL
                    $customer->paket->kecepatan_paket ?? '-', $customer->active_user ?? '-',
                    $customer->deviceSn->deviceModel->nama_model ?? '-', $customer->deviceSn->nomor ?? '-',
                    $customer->ip_ppoe ?? '-', $customer->ip_onu ?? '-',
                    $customer->tanggal_aktivasi ? Carbon::parse($customer->tanggal_aktivasi)->format('d/m/Y') : '-',
                    Str::title(str_replace('_', ' ', $customer->status)),
                ];
            }
        } else {
            $noDataRow               = array_fill(0, count($this->tableHeaders), null);
            $noDataRow[0]            = 'Tidak ada data pelanggan yang cocok dengan filter Anda.';
            $this->exportDataArray[] = $noDataRow;
        }
    }

    public function array(): array
    {
        return $this->exportDataArray;
    }

    public function styles(Worksheet $sheet)
    {
        $mainTableColumnCount = count($this->tableHeaders);
        $lastColumnLetter     = Coordinate::stringFromColumnIndex($mainTableColumnCount);

        // Base style
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(9);

        // Title styling
        $titleStyle = [
            'font'      => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFD9'],
            ],
        ];
        $sheet->mergeCells("A{$this->titleRow}:{$lastColumnLetter}{$this->titleRow}");
        $sheet->getStyle("A{$this->titleRow}")->applyFromArray($titleStyle);

        // Filter info styling
        $filterStyle = [
            'font'      => [
                'italic' => true,
                'size'   => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA'],
            ],
        ];
        $sheet->mergeCells("A{$this->filterInfoRow}:{$lastColumnLetter}{$this->filterInfoRow}");
        $sheet->getStyle("A{$this->filterInfoRow}")->applyFromArray($filterStyle);

        // Header styling
        $headerStyle = [
            'font'      => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle("A{$this->headerRowStart}:{$lastColumnLetter}{$this->headerRowStart}")
            ->applyFromArray($headerStyle);

        // Data styling
        $dataStyle = [
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        $dataRange = "A" . ($this->headerRowStart + 1) . ":{$lastColumnLetter}" . $sheet->getHighestRow();
        $sheet->getStyle($dataRange)->applyFromArray($dataStyle);

        // Add zebra striping
        for ($row = $this->headerRowStart + 1; $row <= $sheet->getHighestRow(); $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:{$lastColumnLetter}{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F5F5F5');
            }
        }

        // Terapkan style reguler ke kolom foto
        $sheet->getStyle('F:G')->applyFromArray([
            'font'      => [
                'color'     => ['rgb' => 'FFFFF'],
                'underline' => false,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        return $sheet;
    }

    public function title(): string
    {
        return 'Data Pelanggan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set row heights for title and filter info
                $sheet->getRowDimension($this->titleRow)->setRowHeight(20);
                $sheet->getRowDimension($this->filterInfoRow)->setRowHeight(14);

                // Set header row height
                $sheet->getRowDimension($this->headerRowStart)->setRowHeight(18);

                // Set column widths
                $columnWidths = [
                    'A' => 15, // ID Pelanggan
                    'B' => 25, // Nama
                    'C' => 20, // NIK
                    'D' => 35, // Alamat
                    'E' => 15, // No. WA
                    'F' => 20, // Path Foto KTP
                    'G' => 20, // Path Foto Rumah
                    'H' => 15, // Paket
                    'I' => 15, // User Aktif
                    'J' => 20, // Model Perangkat
                    'K' => 20, // SN Perangkat
                    'L' => 15, // IP PPPoE
                    'M' => 15, // IP ONU
                    'N' => 15, // Tgl Aktivasi
                    'O' => 15, // Status
                ];

                foreach ($columnWidths as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }

                // Freeze panes for better navigation
                $sheet->freezePane('A4');
            },
        ];
    }
}
