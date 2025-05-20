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
            font-size: 9px;
        }

        /* DejaVu Sans untuk karakter non-latin, font lebih kecil */
        .container-fluid {
            width: 100%;
            padding: 10px;
        }

        .report-title {
            text-align: center;
            margin-bottom: 15px;
        }

        .report-title h2 {
            margin: 0;
            font-size: 1.3em;
        }

        .report-title p {
            margin: 3px 0;
            font-size: 0.8em;
            color: #555;
        }

        .table-report {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .table-report th,
        .table-report td {
            border: 1px solid #999;
            /* Border lebih jelas untuk PDF */
            padding: 4px 5px;
            text-align: left;
            font-size: 0.8em;
            word-wrap: break-word;
        }

        .table-report thead th {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
        }

        .table-report tbody td.customer-name {
            min-width: 100px;
        }

        .table-report tbody td.customer-id {
            min-width: 60px;
        }

        .table-report tbody td.customer-info {
            min-width: 80px;
        }

        .table-report .month-col {
            text-align: center;
            min-width: 60px;
        }

        .table-report .month-col .status-text {
            display: block;
            font-size: 0.9em;
        }

        .table-report .month-col .sub-text {
            font-size: 0.75em;
            color: #444;
            display: block;
        }


        .status-paid {
            color: green;
            font-weight: bold;
        }

        .status-unpaid {
            color: #c67c00;
        }

        /* Warna oranye lebih tua */
        .status-pending_confirmation {
            color: #0069d9;
        }

        /* Biru lebih tua */
        .status-failed {
            color: #dc3545;
        }

        .status-cancelled {
            color: #6c757d;
        }

        .status-menunggak {
            color: #dc3545;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        /* Hilangkan elemen yang tidak perlu di PDF */
        .no-print,
        .pagination,
        .alert,
        form {
            display: none !important;
        }

        @page {
            margin: 20px;
        }

        /* Margin halaman PDF */
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
            <p style="font-size: 0.7em;">Dicetak pada:
                {{ \Carbon\Carbon::now()->locale('id')->setTimezone('Asia/Pontianak')->translatedFormat('d F Y, H:i') }}
            </p>
        </div>

        @if ($reportData->isNotEmpty())
            <table class="table-report">
                <thead>
                    <tr>
                        <th rowspan="2" style="vertical-align: middle;">ID Pel.</th>
                        <th rowspan="2" style="vertical-align: middle;">Nama Pelanggan</th>
                        <th rowspan="2" style="vertical-align: middle;">Status</th>
                        <th rowspan="2" style="vertical-align: middle;">Paket</th>
                        <th rowspan="2" style="vertical-align: middle;">Tgl Aktivasi</th>
                        <th rowspan="2" style="vertical-align: middle;">Layanan Habis Terakhir</th>
                        @foreach ($displayedMonths as $monthNumber => $monthName)
                            <th colspan="1" class="month-col">{{ $monthName }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($displayedMonths as $monthName)
                            <th class="month-col" style="font-size:0.6em;">TglByr | Status | Inv</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportData as $data)
                        <tr>
                            <td class="customer-id">{{ $data['customer']->id_customer }}</td>
                            <td class="customer-name">{{ $data['customer']->nama_customer }}</td>
                            <td class="text-center">
                                {{ Str::title(str_replace('_', ' ', $data['customer']->status)) }}
                            </td>
                            <td class="customer-info">{{ $data['paket_info'] }}</td>
                            <td class="text-center customer-info">{{ $data['tgl_aktivasi'] }}</td>
                            <td class="text-center customer-info">
                                {{ $data['tgl_layanan_habis_terakhir_visual'] }}
                                @if ($data['tgl_layanan_habis_sebenarnya'])
                                    <br><small style="font-size:0.7em;">(s/d
                                        {{ $data['tgl_layanan_habis_sebenarnya'] }})</small>
                                @endif
                            </td>
                            @foreach ($displayedMonths as $monthNameKey => $monthDisplayName)
                                @php $monthData = $data['monthly_status'][$monthDisplayName] ?? ['text' => '-', 'class' => 'text-muted', 'tgl_bayar' => null, 'invoice_no' => null, 'payment_id' => null]; @endphp
                                <td class="month-col {{ $monthData['class'] }}">
                                    @if ($monthData['tgl_bayar'])
                                        <span class="sub-text">{{ $monthData['tgl_bayar'] }}</span>
                                    @endif
                                    <span class="status-text">{{ $monthData['text'] }}</span>

                                    @if (!empty($monthData['invoice_no']) && $monthData['text'] != '-')
                                        <span
                                            class="sub-text">{{ Str::limit($monthData['invoice_no'], 10, '...') }}</span>
                                    @elseif($monthData['text'] == '-' && empty($monthData['tgl_bayar']))
                                        <span class="sub-text">&nbsp;</span>
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
