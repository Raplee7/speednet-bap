<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle ?? 'Laporan Keuangan Pendapatan' }} - {{ $reportPeriodLabel ?? 'Laporan' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            /* Penting untuk karakter non-latin */
            margin: 0;
            padding: 0;
            font-size: 9pt;
            /* Ukuran font dasar untuk PDF */
        }

        .container-fluid {
            width: 100%;
            padding: 15px 10px;
        }

        .report-header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }

        .report-header h1 {
            margin: 0 0 5px 0;
            font-size: 16pt;
            font-weight: bold;
        }

        .report-header p {
            margin: 3px 0;
            font-size: 10pt;
            color: #333;
        }

        .summary-section {
            margin-bottom: 25px;
            padding: 12px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        .summary-section .title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 5px;
            color: #0056b3;
            /* Warna biru yang lebih gelap */
        }

        .summary-section .amount {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            color: #28a745;
            /* Warna hijau untuk pendapatan */
        }

        .table-report {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table-report th,
        .table-report td {
            border: 1px solid #aaa;
            /* Border lebih jelas */
            padding: 6px 8px;
            /* Padding lebih nyaman */
            text-align: left;
            font-size: 9pt;
        }

        .table-report thead th {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
            /* Header tabel di tengah */
        }

        .table-report td.amount,
        .table-report th.amount {
            text-align: right;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 1px solid #999;
            padding-bottom: 6px;
            color: #333;
        }

        .text-muted {
            color: #6c757d;
        }

        .fw-bold {
            font-weight: bold;
        }

        .text-end {
            text-align: right;
        }

        @page {
            margin: 25mm;
            /* Margin halaman PDF */
            size: a4 portrait;
            /* Ukuran dan orientasi kertas */
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="report-header">
            <h1>{{ $pageTitle ?? 'Laporan Keuangan Pendapatan' }}</h1>
            @if (isset($reportPeriodLabel) && !empty($reportPeriodLabel))
                <p>Periode: <strong>{{ $reportPeriodLabel }}</strong></p>
            @endif
            <p style="font-size: 0.8em;">Dicetak pada:
                {{ \Carbon\Carbon::now()->locale('id')->setTimezone('Asia/Pontianak')->translatedFormat('d F Y, H:i T') }}
            </p>
        </div>

        @if (isset($reportPeriodLabel) && !empty($reportPeriodLabel))
            <div class="summary-section">
                <h3 class="title">Total Pendapatan Keseluruhan</h3>
                <p class="amount">Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}</p>
                @if (isset($previousPeriodIncome) && $previousPeriodIncome !== null)
                    @php
                        $percentageChange = 0;
                        if ($previousPeriodIncome > 0) {
                            $percentageChange = (($totalIncome - $previousPeriodIncome) / $previousPeriodIncome) * 100;
                        } elseif ($totalIncome > 0) {
                            $percentageChange = 100;
                        }
                    @endphp
                    <p style="font-size: 9pt; margin-top: 5px;"
                        class="{{ $percentageChange >= 0 ? 'text-success' : 'text-danger' }}">
                        @if ($percentageChange > 0)
                            <span style="color:green;">&#x25B2;</span> {{-- Panah atas hijau --}}
                        @elseif ($percentageChange < 0)
                            <span style="color:red;">&#x25BC;</span> {{-- Panah bawah merah --}}
                        @endif
                        {{ number_format(abs($percentageChange), 1) }}% dari periode sebelumnya (Rp
                        {{ number_format($previousPeriodIncome, 0, ',', '.') }})
                    </p>
                @endif
            </div>

            <div class="section-title">Rincian Pendapatan per Paket</div>
            @if (isset($incomeByPaket) && $incomeByPaket->count() > 0)
                <table class="table-report">
                    <thead>
                        <tr>
                            <th>Kecepatan Internet</th>
                            <th class="amount">Jumlah Transaksi</th>
                            <th class="amount">Total Pendapatan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($incomeByPaket as $item)
                            <tr>
                                <td>{{ $item->kecepatan_paket }}</td>
                                <td class="amount">{{ $item->transaction_count }}</td>
                                <td class="amount">{{ number_format($item->total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Tidak ada data pendapatan per paket untuk periode ini.</p>
            @endif

            <div class="section-title">Rincian Pendapatan per Metode Pembayaran</div>
            @if (isset($incomeByMethod) && $incomeByMethod->count() > 0)
                <table class="table-report">
                    <thead>
                        <tr>
                            <th>Metode Pembayaran</th>
                            <th class="amount">Jumlah Transaksi</th>
                            <th class="amount">Total Pendapatan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($incomeByMethod as $metode => $data)
                            <tr>
                                <td>{{ Str::title($metode) }}</td>
                                <td class="amount">{{ $data['count'] }}</td>
                                <td class="amount">{{ number_format($data['total'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Tidak ada data pendapatan per metode pembayaran untuk periode ini.</p>
            @endif
        @else
            <p style="text-align:center; padding: 20px;">Silakan pilih filter periode untuk menampilkan laporan.</p>
        @endif
    </div>
</body>

</html>
