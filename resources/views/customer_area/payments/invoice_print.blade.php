<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $payment->nomor_invoice }}</title>
    <link href="{{ asset('cust-assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet"> {{-- Bootstrap untuk styling dasar --}}
    <style>
        body {
            font-family: 'Arial', sans-serif;
            /* Font yang umum dan mudah dibaca */
            color: #333;
            margin: 0;
            padding: 0;
        }

        .invoice-container {
            width: 80mm;
            /* Lebar umum struk thermal, sesuaikan jika perlu */
            max-width: 100%;
            margin: 20px auto;
            padding: 15px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }

        .invoice-header img {
            max-height: 60px;
            /* Sesuaikan tinggi logo Anda */
            margin-bottom: 10px;
        }

        .invoice-header h4 {
            margin: 0;
            font-size: 1.2em;
            font-weight: bold;
        }

        .invoice-header p {
            margin: 2px 0;
            font-size: 0.8em;
        }

        .invoice-details,
        .customer-details,
        .payment-summary {
            margin-bottom: 15px;
            font-size: 0.9em;
        }

        .invoice-details dt,
        .customer-details dt {
            font-weight: bold;
            width: 120px;
            /* Sesuaikan lebar label */
            float: left;
            clear: left;
        }

        .invoice-details dd,
        .customer-details dd {
            margin-left: 130px;
            /* Sesuaikan agar value sejajar */
            margin-bottom: 3px;
            display: block;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 0.9em;
        }

        .items-table th,
        .items-table td {
            border-bottom: 1px solid #eee;
            padding: 8px 5px;
            text-align: left;
        }

        .items-table th {
            background-color: #f8f8f8;
            font-weight: bold;
        }

        .items-table td.amount,
        .items-table th.amount {
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
            font-size: 1.1em;
            border-top: 2px solid #333;
        }

        .payment-method-info {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            font-size: 0.85em;
        }

        .footer-note {
            text-align: center;
            margin-top: 20px;
            font-size: 0.8em;
            color: #777;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }

        @media print {
            body {
                background-color: #fff;
                /* Hilangkan background saat print */
                -webkit-print-color-adjust: exact;
                /* Memaksa print background color di Chrome/Safari */
                print-color-adjust: exact;
            }

            .invoice-container {
                width: 100%;
                /* Gunakan lebar penuh saat print */
                margin: 0;
                padding: 0;
                border: none;
                box-shadow: none;
            }

            .no-print {
                /* Kelas untuk menyembunyikan elemen saat print */
                display: none !important;
            }

            /* Anda bisa menambahkan style print lainnya di sini */
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <div class="invoice-header">
            @if (file_exists(public_path('cust-assets/img/speednet-logo.png')))
                <img src="{{ asset('cust-assets/img/speednet-logo.png') }}"
                    alt="Logo {{ config('app.name', 'Speednet BAP') }}">
            @endif
            <h4>Speednet</h4>
            <p>Jl. Pd. Indah Lestari No.12B, Kec. Sungai Raya, Kab. Kubu Raya, Kalimantan Barat 78391</p>
            <p>Telepon: xxxx | Email: xxx</p>
        </div>

        <div class="text-center mb-3">
            <h5 class="mb-0">STRUK PEMBAYARAN</h5>
            <small>No. Invoice: {{ $payment->nomor_invoice }}</small>
        </div>

        <dl class="invoice-details">
            <dt>Tanggal Cetak:</dt>
            <dd>{{ \Carbon\Carbon::now()->locale('id')->setTimezone('Asia/Pontianak')->translatedFormat('d M Y, H:i') }}
            </dd>
            <dt>Tanggal Lunas:</dt>
            <dd>{{ $payment->tanggal_pembayaran ? \Carbon\Carbon::parse($payment->tanggal_pembayaran)->locale('id')->setTimezone('Asia/Pontianak')->translatedFormat('d M Y, H:i') : '-' }}
            </dd>
        </dl>

        <dl class="customer-details">
            <dt>Pelanggan:</dt>
            <dd>{{ $payment->customer->nama_customer ?? '-' }}</dd>
            <dt>ID Pelanggan:</dt>
            <dd>{{ $payment->customer->id_customer ?? '-' }}</dd>
        </dl>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Deskripsi Layanan</th>
                    <th class="amount">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Pembayaran Layanan Internet
                        @if ($payment->paket)
                            <br><small>Paket:
                                {{ $payment->paket->nama_paket ?? $payment->paket->kecepatan_paket }}</small>
                        @endif
                        <br><small>Periode:
                            {{ \Carbon\Carbon::parse($payment->periode_tagihan_mulai)->locale('id')->translatedFormat('d M Y') }}
                            -
                            {{ \Carbon\Carbon::parse($payment->periode_tagihan_selesai)->addDay()->locale('id')->translatedFormat('d M Y') }}</small>
                    </td>
                    <td class="amount">Rp {{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td class="amount"><strong>TOTAL</strong></td>
                    <td class="amount"><strong>Rp {{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}</strong>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="payment-method-info">
            <strong>Metode Pembayaran:</strong> {{ Str::title($payment->metode_pembayaran ?? '-') }}<br>
            @if ($payment->metode_pembayaran == 'transfer' && $payment->ewallet)
                <strong>Dibayar ke:</strong> {{ $payment->ewallet->nama_ewallet }}
                ({{ $payment->ewallet->no_ewallet }} a/n {{ $payment->ewallet->atas_nama }})<br>
            @endif
            <strong>Status:</strong> <span class="text-success fw-bold">LUNAS</span>
        </div>

        <div class="footer-note">
            Terima kasih telah melakukan pembayaran.
            <br>Simpan struk ini sebagai bukti pembayaran yang sah.
            <div class="mt-2 no-print">
                <button onclick="window.print()" class="btn btn-sm btn-primary">Cetak Struk</button>
                <a href="{{ route('customer.payments.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </div>
</body>

</html>
