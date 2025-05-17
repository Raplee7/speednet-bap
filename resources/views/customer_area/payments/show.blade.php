@extends('landing.layouts.app') {{-- Atau layout khusus area pelanggan --}}

@section('title', $pageTitle ?? 'Detail Tagihan')

@push('styles')
    <style>
        .customer-content-area {
            padding-top: 120px;
            padding-bottom: 60px;
            min-height: 75vh;
        }

        .invoice-details-card dt {
            font-weight: 600;
            color: #555;
        }

        .invoice-details-card dd {
            color: #333;
        }

        .status-badge-lg {
            /* Badge status yang lebih besar */
            font-size: 1rem;
            padding: .6em .9em;
            font-weight: 600;
        }

        .action-buttons .btn {
            margin-top: 0.5rem;
        }

        .rejection-reason-customer {
            background-color: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 1rem;
            border-radius: .375rem;
            /* rounded-3 */
        }
    </style>
@endpush

@section('content')
    <section class="customer-content-area">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9 col-md-10">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="fw-light mb-0">{{ $pageTitle }}</h2>
                        <a href="{{ route('customer.payments.index') }}"
                            class="btn btn-sm btn-outline-secondary rounded-pill">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-arrow-left-short" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z" />
                            </svg>
                            Kembali ke Daftar Tagihan
                        </a>
                    </div>
                    <hr class="mb-4">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-light-subtle p-4 rounded-top-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 fw-bold text-primary">Invoice #{{ $payment->nomor_invoice }}</h5>
                                    <small class="text-muted">Dibuat pada:
                                        {{ $payment->created_at->locale('id')->translatedFormat('d F Y, H:i') }}</small>
                                </div>
                                @php
                                    $statusClass = '';
                                    $statusText = Str::title(str_replace('_', ' ', $payment->status_pembayaran));
                                    switch ($payment->status_pembayaran) {
                                        case 'unpaid':
                                            $statusClass = 'bg-warning text-dark';
                                            $statusText = 'Belum Bayar';
                                            break;
                                        case 'pending_confirmation':
                                            $statusClass = 'bg-info text-dark';
                                            $statusText = 'Menunggu Konfirmasi';
                                            break;
                                        case 'paid':
                                            $statusClass = 'bg-success text-white';
                                            $statusText = 'Lunas';
                                            break;
                                        case 'failed':
                                            $statusClass = 'bg-danger text-white';
                                            $statusText = 'Gagal';
                                            break;
                                        case 'cancelled':
                                            $statusClass = 'bg-secondary text-white';
                                            $statusText = 'Dibatalkan';
                                            break;
                                    }
                                @endphp
                                <span
                                    class="badge rounded-pill status-badge-lg {{ $statusClass }}">{{ $statusText }}</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <h6 class="text-muted">Ditagihkan Kepada:</h6>
                                    <p class="fw-semibold mb-0">{{ $payment->customer->nama_customer }}</p>
                                    <p class="text-muted small mb-0">{{ $payment->customer->id_customer }}</p>
                                    <p class="text-muted small">{{ $payment->customer->alamat_customer }}</p>
                                </div>
                                @if ($payment->status_pembayaran == 'paid' && $payment->tanggal_pembayaran)
                                    <div class="col-md-6 mb-4 text-md-end">
                                        <h6 class="text-muted">Tanggal Pembayaran:</h6>
                                        <p class="fw-semibold mb-0">
                                            {{ \Carbon\Carbon::parse($payment->tanggal_pembayaran)->locale('id')->setTimezone('Asia/Pontianak')->translatedFormat('d F Y, H:i') }}
                                        </p>
                                        <p class="text-muted small mb-0">Metode:
                                            {{ Str::title($payment->metode_pembayaran ?? '-') }}</p>
                                        @if ($payment->metode_pembayaran == 'transfer' && $payment->ewallet)
                                            <p class="text-muted small mb-0">Ke: {{ $payment->ewallet->nama_ewallet }}</p>
                                        @endif
                                    </div>
                                @else
                                    <div class="col-md-6 mb-4 text-md-end">
                                        <h6 class="text-muted">Batas Pembayaran Invoice:</h6>
                                        <p class="fw-semibold mb-0">
                                            {{ \Carbon\Carbon::parse($payment->tanggal_jatuh_tempo)->locale('id')->translatedFormat('d F Y') }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <h6 class="text-muted mt-2 mb-3">Rincian Layanan:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Deskripsi</th>
                                            <th class="text-center">Durasi</th>
                                            <th class="text-end">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                Layanan Internet @if ($payment->paket)
                                                    - {{ $payment->paket->kecepatan_paket }}
                                                @endif
                                                <br>
                                                <small class="text-muted">
                                                    Periode:
                                                    {{ \Carbon\Carbon::parse($payment->periode_tagihan_mulai)->locale('id')->translatedFormat('d M Y') }}
                                                    &mdash;
                                                    {{ \Carbon\Carbon::parse($payment->periode_tagihan_selesai)->addDay()->locale('id')->translatedFormat('d M Y') }}
                                                    (Aktif s/d akhir hari
                                                    {{ \Carbon\Carbon::parse($payment->periode_tagihan_selesai)->locale('id')->translatedFormat('d M Y') }})
                                                </small>
                                            </td>
                                            <td class="text-center align-middle">{{ $payment->durasi_pembayaran_bulan }}
                                                bulan</td>
                                            <td class="text-end align-middle">Rp
                                                {{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td colspan="2" class="text-end border-0">TOTAL TAGIHAN:</td>
                                            <td class="text-end fs-5 text-danger border-0">Rp
                                                {{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            @if (
                                $payment->bukti_pembayaran &&
                                    ($payment->status_pembayaran == 'pending_confirmation' ||
                                        $payment->status_pembayaran == 'paid' ||
                                        $payment->status_pembayaran == 'failed'))
                                <div class="mt-4">
                                    <h6 class="text-muted">Bukti Pembayaran Anda:</h6>
                                    <a href="#" data-bs-toggle="modal"
                                        data-bs-target="#modalBuktiBayarPelanggan_{{ $payment->id_payment }}">
                                        <img src="{{ asset('storage/' . $payment->bukti_pembayaran) }}"
                                            alt="Bukti Pembayaran" class="img-thumbnail rounded shadow-sm"
                                            style="max-height: 150px; cursor: zoom-in;">
                                    </a>
                                </div>
                                <div class="modal fade" id="modalBuktiBayarPelanggan_{{ $payment->id_payment }}"
                                    tabindex="-1"
                                    aria-labelledby="modalBuktiBayarLabelPelanggan_{{ $payment->id_payment }}"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content rounded-4">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Bukti Pembayaran - {{ $payment->nomor_invoice }}
                                                </h5><button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center p-0"><img
                                                    src="{{ asset('storage/' . $payment->bukti_pembayaran) }}"
                                                    alt="Bukti Pembayaran" class="img-fluid"></div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($payment->status_pembayaran == 'failed' && !empty($payment->catatan_admin))
                                @php
                                    $fullAdminNote = $payment->catatan_admin;
                                    $displayReason = $fullAdminNote;
                                    $reasonPrefixSpecific = '[Alasan Penolakan]: ';
                                    $posSpecific = strrpos($fullAdminNote, $reasonPrefixSpecific);
                                    if ($posSpecific !== false) {
                                        $displayReason = trim(
                                            substr($fullAdminNote, $posSpecific + strlen($reasonPrefixSpecific)),
                                        );
                                    } else {
                                        $adminVerificationPrefixes = [
                                            "\n[Verifikasi Admin]: ",
                                            '[Verifikasi Admin]: ',
                                            "\n[Verifikasi Ditolak]: ",
                                            '[Verifikasi Ditolak]: ',
                                        ];
                                        $lastKnownPrefixPos = -1;
                                        $prefixLength = 0;
                                        foreach ($adminVerificationPrefixes as $prefix) {
                                            $currentPos = strrpos($fullAdminNote, $prefix);
                                            if ($currentPos !== false) {
                                                if ($currentPos > $lastKnownPrefixPos) {
                                                    $lastKnownPrefixPos = $currentPos;
                                                    $prefixLength = strlen($prefix);
                                                }
                                            }
                                        }
                                        if ($lastKnownPrefixPos !== -1) {
                                            $displayReason = trim(
                                                substr($fullAdminNote, $lastKnownPrefixPos + $prefixLength),
                                            );
                                        }
                                    }
                                @endphp
                                <div class="mt-4 rejection-reason-customer">
                                    <h6 class="fw-bold text-danger">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-exclamation-circle-fill me-1"
                                            viewBox="0 0 16 16">
                                            <path
                                                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z" />
                                        </svg>
                                        Pembayaran Ditolak
                                    </h6>
                                    <p class="mb-1"><strong>Alasan dari Admin:</strong></p>
                                    <p class="mb-0" style="white-space: pre-wrap;">{{ $displayReason }}</p>
                                    <small class="d-block mt-2">Silakan hubungi layanan pelanggan kami atau coba lakukan
                                        pembayaran kembali dengan data yang benar.</small>
                                </div>
                            @endif

                            <hr class="my-4">
                            <div class="action-buttons text-center">
                                @if ($payment->status_pembayaran == 'unpaid')
                                    <a href="{{ route('customer.renewal.form', ['invoice' => $payment->nomor_invoice]) }}"
                                        class="btn btn-success rounded-pill px-4 py-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-credit-card-2-front-fill me-1"
                                            viewBox="0 0 16 16">
                                            <path
                                                d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2.5 1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-2zm0 3a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1h-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1h-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1h-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1h-1z" />
                                        </svg>
                                        Bayar Tagihan Ini / Upload Bukti
                                    </a>
                                @elseif($payment->status_pembayaran == 'paid')
                                    <a href="{{ route('customer.payments.print_invoice', $payment->id_payment) }}"
                                        target="_blank" class="btn btn-primary rounded-pill px-4 py-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-printer-fill me-1" viewBox="0 0 16 16">
                                            <path
                                                d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z" />
                                            <path
                                                d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z" />
                                        </svg>
                                        Cetak Struk
                                    </a>
                                @elseif($payment->status_pembayaran == 'failed')
                                    <a href="{{ route('customer.renewal.form') }}?invoice={{ $payment->nomor_invoice }}"
                                        class="btn btn-warning rounded-pill px-4 py-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-arrow-repeat me-1" viewBox="0 0 16 16">
                                            <path
                                                d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z" />
                                            <path fill-rule="evenodd"
                                                d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.5A5.002 5.002 0 0 0 8 3zM3.143 6.146A6.002 6.002 0 0 1 8 2.083c1.15 0 2.165.349 3.004.918a.5.5 0 1 1-.592.812A5.002 5.002 0 0 0 8 3.083zM12.857 9.854A6.002 6.002 0 0 1 8 13.917c-1.15 0-2.165-.349-3.004-.918a.5.5 0 0 1 .592-.812A5.002 5.002 0 0 0 8 12.917z" />
                                        </svg>
                                        Coba Bayar Ulang / Upload Bukti Baru
                                    </a>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
