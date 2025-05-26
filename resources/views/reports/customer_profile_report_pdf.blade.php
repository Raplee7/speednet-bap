<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle ?? 'Laporan Data Pelanggan' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 7px;
        }

        /* Font lebih kecil */
        .container-fluid {
            width: 100%;
            padding: 8px;
        }

        .report-header {
            text-align: center;
            margin-bottom: 12px;
        }

        .report-header h2 {
            margin: 0 0 2px 0;
            font-size: 1.2em;
        }

        /* Ukuran judul lebih kecil */
        .report-header p {
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
            /* Font data tabel sangat kecil agar muat banyak kolom */
            word-wrap: break-word;
            vertical-align: top;
        }

        .table-report thead th {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
            padding: 3px 2px;
            /* Padding header lebih kecil */
        }

        .text-center {
            text-align: center !important;
        }

        .wrap-text {
            white-space: normal;
        }

        /* Untuk alamat */

        @page {
            margin: 10mm 5mm;
            /* Margin lebih kecil */
            size: a4 landscape;
        }

        /* Styling untuk cell path foto */
        .table-report td:nth-child(6),
        .table-report td:nth-child(7) {
            font-size: 0.55em;
            /* Ukuran font lebih kecil untuk path */
            word-break: break-all;
            /* Memecah teks path yang panjang */
            max-width: 100px;
            /* Batasi lebar maksimal */
            color: #0066cc;
        }

        /* Hover tooltip untuk path panjang */
        .path-cell {
            position: relative;
            color: #0066cc;
            text-decoration: underline;
            cursor: pointer;
        }

        .path-cell:hover::after {
            content: attr(data-full-path);
            position: absolute;
            bottom: 100%;
            left: 0;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7em;
            white-space: nowrap;
            z-index: 1000;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="report-header">
            <h2>{{ $pageTitle }}</h2>
            <div class="filter-info">
                <strong>Filter Aktif:</strong><br>
                @php $hasActiveFilter = false; @endphp

                @if (!empty($filterInfo))
                    @if (!empty($filterInfo['search_query']))
                        Cari: {{ $filterInfo['search_query'] }};<br>
                        @php $hasActiveFilter = true; @endphp
                    @endif

                    @if (!empty($filterInfo['status_pelanggan']))
                        Status: {{ $filterInfo['status_pelanggan'] }};<br>
                        @php $hasActiveFilter = true; @endphp
                    @endif

                    @if (!empty($filterInfo['paket_info']))
                        Paket: {{ $filterInfo['paket_info'] }};<br>
                        @php $hasActiveFilter = true; @endphp
                    @endif

                    @if (!empty($filterInfo['activation_reportPeriodLabel']))
                        @if (
                            $filterInfo['activation_reportPeriodLabel'] !== 'Semua Periode' &&
                                !Str::contains($filterInfo['activation_reportPeriodLabel'], ['Tidak Valid', 'Tidak Lengkap', 'Error Filter']))
                            Periode Aktivasi: {{ $filterInfo['activation_reportPeriodLabel'] }};<br>
                            @php $hasActiveFilter = true; @endphp
                        @endif
                    @endif

                    @if (!$hasActiveFilter)
                        Tidak ada filter yang diterapkan
                    @endif
                @else
                    Tidak ada filter yang diterapkan
                @endif
            </div>
            <p style="font-size: 0.7em;">Dicetak pada:
                {{ \Carbon\Carbon::now()->locale('id')->setTimezone('Asia/Pontianak')->translatedFormat('d F Y, H:i') }}
            </p>
        </div>

        @if ($customers->isNotEmpty())
            <table class="table-report">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th class="wrap-text">Alamat</th>
                        <th>WA</th>
                        <th>KTP</th>
                        <th>Rumah</th>
                        <th>Paket</th>
                        <th>User Aktif</th>
                        <th>Model</th>
                        <th>SN</th>
                        <th>IP PPPoE</th>
                        <th>IP ONU</th>
                        <th>Tgl Aktivasi</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                {{-- Ubah bagian tbody dalam table --}}
                <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <td>{{ $customer->id_customer }}</td>
                            <td>{{ Str::limit($customer->nama_customer, 20) }}</td>
                            <td>{{ $customer->nik_customer ?? '-' }}</td>
                            <td class="wrap-text">{{ Str::limit($customer->alamat_customer, 30) }}</td>
                            <td>{{ $customer->wa_customer ?? '-' }}</td>
                            <td class="path-cell" data-full-path="{{ asset('storage/' . $customer->foto_ktp_customer) ?: '-' }}">
                                @if($customer->foto_ktp_customer)
                                    {{ url('storage/' . $customer->foto_ktp_customer) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="path-cell" data-full-path="{{ asset('storage/' . $customer->foto_timestamp_rumah) ?: '-' }}">
                                @if($customer->foto_timestamp_rumah)
                                    {{ url('storage/' . $customer->foto_timestamp_rumah) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $customer->paket->kecepatan_paket ?? '-' }}</td>
                            <td>{{ $customer->active_user ?? '-' }}</td>
                            <td>{{ Str::limit($customer->deviceSn->deviceModel->nama_model ?? '-', 15) }}</td>
                            <td>{{ Str::limit($customer->deviceSn->nomor ?? '-', 15) }}</td>
                            <td>{{ $customer->ip_ppoe ?? '-' }}</td>
                            <td>{{ $customer->ip_onu ?? '-' }}</td>
                            <td>{{ $customer->tanggal_aktivasi ? \Carbon\Carbon::parse($customer->tanggal_aktivasi)->format('d/m/y') : '-' }}
                            </td>
                            <td class="text-center">{{ Str::title(str_replace('_', ' ', $customer->status)) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align:center; padding: 20px;">Tidak ada data pelanggan yang cocok dengan filter Anda.</p>
        @endif
    </div>
</body>

</html>
