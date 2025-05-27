<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle ?? 'Laporan Pembayaran Pelanggan' }} - {{ $selectedYear }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 7px;
        }

        .container-fluid {
            width: 100%;
            padding: 8px;
        }

        .report-title {
            text-align: center;
            margin-bottom: 12px;
        }

        .report-title h2 {
            margin: 0 0 2px 0;
            font-size: 1.2em;
        }

        .report-title p {
            margin: 1px 0;
            font-size: 0.7em;
            color: #555;
        }

        .filter-info {
            font-size: 0.65em;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px dotted #ccc;
            text-align: left;
            line-height: 1.3;
        }

        .table-report {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .table-report th,
        .table-report td {
            border: 1px solid #888;
            padding: 2px 3px;
            text-align: left;
            font-size: 0.6em;
            word-wrap: break-word;
            vertical-align: top;
        }

        .table-report thead th {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
            padding: 3px 2px;
            height: 4mm;
            vertical-align: middle;
        }

        /* Column Widths */
        .customer-id {
            width: 30pt;
        }

        .customer-name {
            width: 60pt;
        }

        .customer-status {
            width: 25pt;
        }

        .customer-info {
            width: 40pt;
        }

        .month-col {
            width: 35pt;
            padding: 1mm !important;
            text-align: center;
        }

        /* Status Colors */
        .status-paid {
            color: #048848;
        }

        .status-unpaid {
            color: #c05621;
        }

        .status-pending_confirmation {
            color: #2b6cb0;
        }

        .status-failed {
            color: #c53030;
        }

        .status-cancelled {
            color: #718096;
        }

        .status-menunggak {
            color: #c53030;
            font-weight: bold;
        }

        /* Month Column Styling */
        .month-col .status-text {
            font-size: 0.6em;
            line-height: 1.2;
            margin: 0.5mm 0;
            display: block;
        }

        .month-col .sub-text {
            font-size: 0.55em;
            line-height: 1.1;
            display: block;
            margin: 0.3mm 0;
        }

        /* Utilities */
        .text-center {
            text-align: center !important;
        }

        .text-truncate {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .wrap-text {
            white-space: normal;
        }

        /* Page Settings */
        @page {
            margin: 10mm 5mm;
            size: a4 landscape;
        }

        /* Small Text */
        small {
            font-size: 0.55em;
        }

        /* Invoice Number Style */
        .invoice-number {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 0.55em;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="report-title">
            <h2>{{ $pageTitle }}</h2>
            @if ($selectedYear && $selectedStartMonth && $selectedEndMonth)
                <p>Periode: <strong>{{ $allMonthNames[$selectedStartMonth] }} - {{ $allMonthNames[$selectedEndMonth] }}
                        {{ $selectedYear }}</strong></p>
            @endif
            <p>Dicetak pada:
                {{ \Carbon\Carbon::now()->locale('id')->setTimezone('Asia/Pontianak')->translatedFormat('d F Y, H:i') }}
            </p>
        </div>

        @if ($reportData->isNotEmpty())
            <table class="table-report">
                <thead>
                    <tr>
                        <th rowspan="2">ID</th>
                        <th rowspan="2">Nama</th>
                        <th rowspan="2">Sts</th>
                        <th rowspan="2">Pkt</th>
                        <th rowspan="2">Tgl<br>Aktif</th>
                        <th rowspan="2">Layan<br>Habis</th>
                        @foreach ($displayedMonths as $monthNumber => $monthName)
                            <th class="month-col">{{ substr($monthName, 0, 3) }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($displayedMonths as $monthName)
                            <th class="month-col">Tgl|St|Inv</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportData as $data)
                        <tr>
                            <td class="customer-id text-center">{{ $data['customer']->id_customer }}</td>
                            <td class="customer-name">{{ Str::limit($data['customer']->nama_customer, 20) }}</td>
                            <td class="customer-status text-center">  <!-- Change this line -->
                                <span class="status-{{ strtolower($data['customer']->status) }}">
                                    {{ Str::title(str_replace('_', ' ', $data['customer']->status)) }}
                                </span>
                            </td>
                            <td class="customer-info">{{ $data['paket_info'] }}</td>
                            <td class="text-center customer-info">{{ $data['tgl_aktivasi'] }}</td>
                            <td class="text-center customer-info">
                                {{ $data['tgl_layanan_habis_terakhir_visual'] }}
                            </td>
                            @foreach ($displayedMonths as $monthNameKey => $monthDisplayName)
                                @php
                                    $monthData = $data['monthly_status'][$monthDisplayName] ?? [
                                        'text' => '-',
                                        'class' => 'text-muted',
                                        'tgl_bayar' => null,
                                        'invoice_no' => null,
                                    ];
                                    $tanggalBayar = null;
                                    if ($monthData['tgl_bayar']) {
                                        try {
                                            $tanggalBayar = \Carbon\Carbon::createFromFormat(
                                                'd/m/Y',
                                                $monthData['tgl_bayar'],
                                            )->format('d/m');
                                        } catch (\Exception $e) {
                                            $tanggalBayar = '-';
                                        }
                                    }
                                @endphp
                                <td class="month-col {{ $monthData['class'] }}">
                                    @if($tanggalBayar && $tanggalBayar != '-')
                                        <span class="sub-text">{{ $tanggalBayar }}</span>
                                    @endif
                                    <span class="status-text">{{ $monthData['text'] }}</span>
                                    @if(!empty($monthData['invoice_no']) && $monthData['text'] != '-')
                                        <span class="sub-text invoice-number">{{ $monthData['invoice_no'] }}</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align:center; padding: 20px;">Tidak ada data untuk periode yang dipilih.</p>
        @endif
    </div>
</body>

</html>
