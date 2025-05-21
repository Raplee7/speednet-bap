<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle ?? 'Laporan Semua Tagihan' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            /* Penting untuk karakter non-latin dan simbol */
            margin: 0;
            padding: 0;
            font-size: 8px;
            /* Ukuran font dasar untuk PDF, bisa disesuaikan */
        }

        .container-fluid {
            width: 100%;
            padding: 10px;
        }

        .report-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .report-header h2 {
            margin: 0 0 3px 0;
            font-size: 14pt;
            /* Ukuran judul utama */
        }

        .report-header p {
            margin: 2px 0;
            font-size: 9pt;
            /* Ukuran teks periode dan cetak */
            color: #333;
        }

        .filter-info {
            font-size: 7pt;
            /* Ukuran font info filter lebih kecil */
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dotted #ccc;
            text-align: left;
            /* Filter info rata kiri */
        }

        .summary-section {
            margin-bottom: 15px;
            font-size: 8pt;
            /* Ukuran font summary */
        }

        .summary-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-section th {
            text-align: left;
            padding: 4px;
            /* Padding lebih kecil */
            background-color: #f0f0f0;
            border: 1px solid #bbb;
            /* Border lebih jelas */
            font-weight: bold;
        }

        .summary-section td {
            text-align: left;
            padding: 4px;
            border: 1px solid #bbb;
        }

        .summary-section td.amount {
            text-align: right;
        }

        .table-report {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-report th,
        .table-report td {
            border: 1px solid #777;
            /* Border lebih jelas untuk tabel utama */
            padding: 3px 4px;
            text-align: left;
            font-size: 7pt;
            /* Font data tabel lebih kecil agar muat banyak kolom */
            word-wrap: break-word;
            vertical-align: top;
        }

        .table-report thead th {
            background-color: #e0e0e0;
            /* Warna header tabel sedikit lebih gelap */
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
        }

        .text-center {
            text-align: center !important;
        }

        .text-end {
            text-align: right !important;
        }

        /* Kelas status untuk PDF (hanya warna teks, background mungkin tidak selalu render baik) */
        .status-paid {
            color: green;
        }

        .status-unpaid {
            color: #D2691E;
        }

        /* Coklat/Oranye tua */
        .status-pending_confirmation {
            color: blue;
        }

        .status-failed {
            color: red;
        }

        .status-cancelled {
            color: #555;
        }

        /* Abu-abu tua */

        @page {
            margin: 15mm 10mm;
            /* Margin halaman PDF */
            size: a4 landscape;
            /* Ukuran dan orientasi kertas */
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="report-header">
            <h2>{{ $pageTitle }}</h2>
            <p class="filter-info">
                <strong>Filter Aktif:</strong>
                @if ($request->hasAny(['start_date', 'end_date', 'status_pembayaran', 'customer_id', 'paket_id', 'search_invoice']))
                    @if ($request->filled('start_date'))
                        Dari Tgl Dibuat: {{ \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') }};
                    @endif
                    @if ($request->filled('end_date'))
                        Sampai Tgl Dibuat: {{ \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') }};
                    @endif
                    @if ($request->filled('status_pembayaran'))
                        Status: {{ $paymentStatuses[$request->status_pembayaran] ?? $request->status_pembayaran }};
                    @endif
                    @if ($request->filled('customer_id'))
                        Pelanggan: {{ $filterInfo['customer_name'] ?? $request->customer_id }};
                    @endif
                    @if ($request->filled('paket_id'))
                        Paket: {{ $filterInfo['paket_info'] ?? $request->paket_id }};
                    @endif
                    @if ($request->filled('search_invoice'))
                        Cari Inv: {{ $request->search_invoice }};
                    @endif
                @else
                    (Tidak ada filter diterapkan)
                @endif
            </p>
            <p style="font-size: 0.8em;">Dicetak pada:
                {{ \Carbon\Carbon::now()->locale('id')->setTimezone('Asia/Pontianak')->translatedFormat('d F Y, H:i T') }}
            </p>
        </div>

        {{-- Summary Section --}}
        @if (
            $request->hasAny(['start_date', 'end_date', 'status_pembayaran', 'customer_id', 'paket_id', 'search_invoice']) ||
                $payments->isNotEmpty())
            <div class="summary-section">
                <h3 style="font-size:1.1em; margin-bottom:5px; text-align:center;">Ringkasan Laporan</h3>
                <table class="table-report">
                    <thead>
                        <tr>
                            <th>Deskripsi Ringkasan</th>
                            <th class="text-end">Jumlah Invoice</th>
                            <th class="text-end">Total Nilai (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total Semua Tagihan (Sesuai Filter)</td>
                            <td class="text-end">{{ $totalInvoices }}</td>
                            <td class="text-end">{{ number_format($totalAmountAll, 0, ',', '.') }}</td>
                        </tr>
                        {{-- PERBAIKAN LOOP SUMMARY --}}
                        @foreach ($paymentStatuses as $statusKey => $statusLabel)
                            @php
                                // $summaryByStatus adalah Collection, ->get($key) akan mengembalikan item (objek stdClass) atau null
                                $summaryDataItem = $summaryByStatus->get($statusKey);
                            @endphp
                            <tr>
                                <td>Tagihan {{ $statusLabel }}</td>
                                {{-- Akses sebagai properti objek --}}
                                <td class="text-end">{{ $summaryDataItem->count ?? 0 }}</td>
                                <td class="text-end">
                                    {{ number_format($summaryDataItem->total_amount ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($payments->isNotEmpty())
            <table class="table-report">
                <thead>
                    <tr>
                        <th>No. Inv</th>
                        <th>Tgl Buat</th>
                        <th>Pelanggan</th>
                        <th>ID Pel.</th>
                        <th>Paket</th>
                        <th>Periode</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-center">Status</th>
                        <th>Tgl Bayar</th>
                        <th>Metode</th>
                        <th>Catatan Admin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr>
                            <td>{{ $payment->nomor_invoice }}</td>
                            <td>{{ $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('d/m/y H:i') : '-' }}
                            </td>
                            <td>{{ Str::limit($payment->customer->nama_customer ?? '-', 20) }}</td>
                            <td>{{ $payment->customer->id_customer ?? '-' }}</td>
                            <td>{{ $payment->paket->kecepatan_paket ?? '-' }}</td>
                            <td>
                                {{ $payment->periode_tagihan_mulai ? \Carbon\Carbon::parse($payment->periode_tagihan_mulai)->format('d/m/y') : '-' }}
                                -
                                {{ $payment->periode_tagihan_selesai ? \Carbon\Carbon::parse($payment->periode_tagihan_selesai)->addDay()->format('d/m/y') : '-' }}
                            </td>
                            <td class="text-end">{{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}</td>
                            <td class="text-center {{ 'status-' . $payment->status_pembayaran }}">
                                {{ Str::title(str_replace('_', ' ', $payment->status_pembayaran)) }}
                            </td>
                            <td>{{ $payment->tanggal_pembayaran ? \Carbon\Carbon::parse($payment->tanggal_pembayaran)->format('d/m/y') : '-' }}
                            </td>
                            <td>{{ $payment->metode_pembayaran ? Str::title($payment->metode_pembayaran) : '-' }}</td>
                            <td>{{ Str::limit($payment->catatan_admin, 25) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($request->hasAny(['start_date', 'end_date', 'status_pembayaran', 'customer_id', 'paket_id', 'search_invoice']))
            <p style="text-align:center; padding: 20px;">Tidak ada data tagihan yang cocok dengan filter Anda.</p>
        @else
            <p style="text-align:center; padding: 20px;">Silakan terapkan filter untuk melihat data.</p>
        @endif
    </div>
</body>

</html>
