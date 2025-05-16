@extends('landing.layouts.app') {{-- Menggunakan layout landing page, atau layout khusus pelanggan jika ada --}}

@section('title', $pageTitle ?? 'Tagihan Saya')

@push('styles')
    <style>
        .customer-content-area {
            padding-top: 120px;
            /* Sesuaikan dengan tinggi header Anda */
            padding-bottom: 60px;
            min-height: 75vh;
        }

        .invoice-card {
            transition: box-shadow 0.3s ease-in-out;
        }

        .invoice-card:hover {
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }

        .status-badge {
            font-size: 0.85em;
            padding: .5em .75em;
        }

        .filter-form .form-select,
        .filter-form .btn {
            font-size: 0.9rem;
        }

        .rejection-reason {
            /* Style untuk alasan penolakan */
            background-color: #fff3cd;
            /* Warna kuning muda, mirip alert warning */
            border-left: 4px solid #ffc107;
            /* Border kuning */
            padding: 10px;
            margin-top: 10px;
            font-size: 0.9em;
            border-radius: .25rem;
        }
    </style>
@endpush

@section('content')
    <section class="customer-content-area">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="fw-light">{{ $pageTitle }}</h2>
                        <a href="{{ route('customer.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-arrow-left-short" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z" />
                            </svg>
                            Kembali ke Dashboard
                        </a>
                    </div>
                    <hr>
                </div>
            </div>

            {{-- Form Filter --}}
            <div class="row mb-4">
                <div class="col-md-6 ms-md-auto">
                    <form action="{{ route('customer.payments.index') }}" method="GET" class="d-flex gap-2 filter-form">
                        <select name="status" class="form-select form-select-sm rounded-pill">
                            <option value="">Semua Status Tagihan</option>
                            @foreach ($paymentStatuses as $value => $label)
                                <option value="{{ $value }}" {{ $filterStatus == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill">Filter</button>
                        @if ($filterStatus)
                            <a href="{{ route('customer.payments.index') }}"
                                class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
                        @endif
                    </form>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show rounded-3 mb-4" role="alert">
                    {!! session('info') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif


            @if ($payments->isEmpty())
                <div class="alert alert-info rounded-3 text-center">
                    <h4 class="alert-heading">Tidak Ada Tagihan</h4>
                    <p>Saat ini Anda tidak memiliki tagihan dengan status yang dipilih.</p>
                </div>
            @else
                @foreach ($payments as $payment)
                    <div class="card shadow-sm border-0 rounded-3 mb-3 invoice-card">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <h6 class="mb-1 text-primary">Invoice #{{ $payment->nomor_invoice }}</h6>
                                    <small class="text-muted">Dibuat:
                                        {{ $payment->created_at->locale('id')->translatedFormat('d M Y') }}</small>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <p class="mb-1">
                                        <span class="fw-semibold">Periode:</span>
                                        {{ \Carbon\Carbon::parse($payment->periode_tagihan_mulai)->locale('id')->translatedFormat('d M Y') }}
                                        -
                                        {{ \Carbon\Carbon::parse($payment->periode_tagihan_selesai)->addDay()->locale('id')->translatedFormat('d M Y') }}
                                    </p>
                                    @if ($payment->paket)
                                        <small class="text-muted">Paket:
                                            {{ $payment->paket->nama_paket ?? $payment->paket->kecepatan_paket }}</small>
                                    @endif
                                </div>
                                <div class="col-md-2 text-md-end mb-3 mb-md-0">
                                    <span class="fw-bold fs-5 text-danger">Rp
                                        {{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}</span>
                                </div>
                                <div class="col-md-3 text-md-center">
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
                                        class="badge rounded-pill status-badge {{ $statusClass }}">{{ $statusText }}</span>

                                    @if ($payment->status_pembayaran == 'unpaid')
                                        <p class="mb-0 mt-1"><small class="text-muted">Batas Bayar:
                                                {{ \Carbon\Carbon::parse($payment->tanggal_jatuh_tempo)->locale('id')->translatedFormat('d M Y') }}</small>
                                        </p>
                                    @elseif($payment->status_pembayaran == 'paid' && $payment->tanggal_pembayaran)
                                        <p class="mb-0 mt-1"><small class="text-muted">Dibayar:
                                                {{ \Carbon\Carbon::parse($payment->tanggal_pembayaran)->locale('id')->translatedFormat('d M Y') }}</small>
                                        </p>
                                    @endif

                                    {{-- Tombol Aksi --}}
                                    <div class="mt-2">
                                        @if ($payment->status_pembayaran == 'unpaid')
                                            <a href="{{ route('customer.renewal.form') }}?invoice={{ $payment->nomor_invoice }}"
                                                class="btn btn-sm btn-success rounded-pill action-button">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="currentColor" class="bi bi-credit-card-fill me-1"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1H0V4zm0 3v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7H0zm3 2h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1z" />
                                                </svg>
                                                Bayar / Upload Bukti
                                            </a>
                                        @elseif ($payment->status_pembayaran == 'pending_confirmation')
                                            <span class="text-info small">Menunggu Verifikasi Admin</span>
                                        @elseif ($payment->status_pembayaran == 'paid')
                                            {{-- TOMBOL CETAK STRUK BARU --}}
                                            <a href="{{ route('customer.payments.print_invoice', $payment->id_payment) }}"
                                                target="_blank"
                                                class="btn btn-sm btn-outline-primary rounded-pill action-button">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="currentColor" class="bi bi-printer-fill me-1" viewBox="0 0 16 16">
                                                    <path
                                                        d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z" />
                                                    <path
                                                        d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z" />
                                                </svg>
                                                Cetak Struk
                                            </a>
                                        @endif
                                    </div>

                                </div>
                            </div>
                            {{-- Menampilkan Alasan Penolakan --}}
                            @if ($payment->status_pembayaran == 'failed' && !empty($payment->catatan_admin))
                                @php
                                    $fullAdminNote = $payment->catatan_admin;
                                    $displayReason = $fullAdminNote; // Default

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
                                <div class="rejection-reason mt-3 mx-3">
                                    <strong>Alasan Penolakan:</strong>
                                    <p class="mb-0" style="white-space: pre-wrap;">{{ $displayReason }}</p>
                                </div>
                            @endif
                            {{-- Akhir Alasan Penolakan --}}
                        </div>
                    </div>
                @endforeach

                {{-- Pagination Links --}}
                @if ($payments->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $payments->appends(request()->query())->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    {{-- Script khusus jika ada --}}
@endpush
