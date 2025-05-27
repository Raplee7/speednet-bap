@extends('landing.layouts.app')

@section('title', $pageTitle ?? 'Dashboard Saya')

@push('styles')
    <style>
        .customer-dashboard-content {
            padding-top: 110px;
            padding-bottom: 60px;
            min-height: 80vh;
        }

        .welcome-banner {
            background: linear-gradient(to right, rgba(13, 110, 253, 0.85), rgba(13, 110, 253, 0.6)),
                url("{{ asset('cust-assets/img/hero-bg.webp') }}") center center;
            background-size: cover;
            color: white;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            bottom: -20px;
            right: -20px;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            z-index: 1;
        }

        .info-card {
            transition: all 0.3s ease;
            border: none !important;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.12) !important;
        }

        .info-card .card-header {
            background-color: #ffffff;
            border-bottom: none;
            padding: 1.5rem 1.5rem 0.5rem;
        }

        .info-card .card-header h5 {
            color: #333;
            font-weight: 700;
        }

        .info-card .card-header .icon-circle {
            width: 42px;
            height: 42px;
            background-color: rgba(13, 110, 253, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }

        .info-card .card-body {
            padding: 1.25rem 1.5rem 1.5rem;
        }

        .info-card .card-body ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .info-card .card-body ul li {
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
        }

        .info-card .card-body ul li:last-child {
            border-bottom: none;
        }

        .info-card .card-body .label {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .info-card .card-body .value {
            font-weight: 600;
            font-size: 1rem;
        }

        .action-button {
            padding: 0.65rem 1.25rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
            border: none;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.35rem 0.5rem rgba(0, 0, 0, 0.15);
        }

        .action-button svg {
            margin-right: 8px;
        }

        .new-invoice-alert {
            border-left: 4px solid #ffc107;
            background-color: rgba(255, 193, 7, 0.1);
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.25rem 0.5rem;
        }

        .card-counter {
            display: flex;
            align-items: center;
            margin-top: 1rem;
        }

        .card-counter .counter-item {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            border-right: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-counter .counter-item:last-child {
            border-right: none;
        }

        .card-counter .counter-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 0.25rem;
        }

        .card-counter .counter-label {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .service-status-active {
            color: #198754;
            font-weight: 700;
        }

        .service-status-inactive {
            color: #dc3545;
            font-weight: 700;
        }

        .period-info {
            background-color: rgba(13, 110, 253, 0.05);
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }

        .renewal-note {
            background-color: rgba(25, 135, 84, 0.05);
            border-left: 3px solid #198754;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-top: 1rem;
        }

        @media (max-width: 767.98px) {
            .customer-dashboard-content {
                padding-top: 90px;
            }

            .welcome-banner .display-5 {
                font-size: 1.75rem;
            }

            .card-counter {
                flex-direction: column;
            }

            .card-counter .counter-item {
                border-right: none;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                padding: 0.5rem 0;
            }

            .card-counter .counter-item:last-child {
                border-bottom: none;
            }
        }
    </style>
@endpush

@section('content')
    <section class="customer-dashboard-content">
        <div class="container">
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($mostRecentUnpaidInvoice)
                <div class="alert new-invoice-alert alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#ffc107"
                                class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
                                <path
                                    d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                            </svg>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="fw-bold mb-1">Tagihan Menunggu Pembayaran</h5>
                            <p class="mb-1">
                                Anda memiliki <span class="fw-bold">{{ $countUnpaidInvoices }} tagihan</span> yang belum
                                dibayar.
                                @if ($mostRecentUnpaidInvoice)
                                    Tagihan terbaru Anda (Invoice
                                    #<strong>{{ $mostRecentUnpaidInvoice->nomor_invoice }}</strong>)
                                    untuk periode
                                    <strong>{{ \Carbon\Carbon::parse($mostRecentUnpaidInvoice->periode_tagihan_mulai)->locale('id')->translatedFormat('d M Y') }}
                                        &mdash;
                                        {{ \Carbon\Carbon::parse($mostRecentUnpaidInvoice->periode_tagihan_selesai)->addDay()->locale('id')->translatedFormat('d M Y') }}</strong>
                                @endif
                            </p>
                            <a href="{{ route('customer.payments.index', ['status' => 'unpaid']) }}"
                                class="btn btn-warning btn-sm rounded-pill fw-semibold mt-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor"
                                    class="bi bi-credit-card-2-front-fill me-1" viewBox="0 0 16 16">
                                    <path
                                        d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2.5 1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-2zm0 3a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1h-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1h-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1h-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1h-1z" />
                                </svg>
                                Lihat & Bayar Tagihan
                            </a>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="welcome-banner p-4 p-md-5 mb-4">
                <div class="col-lg-7 col-md-8 px-0 position-relative" style="z-index: 2;">
                    <h1 class="display-5 fw-bold text-white mb-3">Halo, {{ Str::words($customer->nama_customer, 2, '') }}!
                    </h1>
                    <p class="lead mb-4">Selamat datang di Dashboard Pelanggan Speednet. Di sini Anda dapat mengelola
                        layanan dan melihat informasi tagihan dengan mudah.</p>
                    @php
                        $latestPayment = $customer->latestPaidPayment();
                        $isActive = false;

                        if ($latestPayment) {
                            $periodeSelesaiDb = \Carbon\Carbon::parse($latestPayment->periode_tagihan_selesai);
                            $isActive = now()->startOfDay()->lte($periodeSelesaiDb);
                        }
                    @endphp

                    <div
                        class="d-inline-block bg-white text-{{ $isActive ? 'success' : 'danger' }} rounded-pill px-3 py-2 fw-semibold shadow-sm">
                        <div class="d-flex align-items-center">
                            <span class="d-inline-block rounded-circle {{ $isActive ? 'bg-success' : 'bg-danger' }} me-2"
                                style="width: 8px; height: 8px;"></span>
                            Status: {{ $isActive ? 'Aktif' : 'Tidak Aktif' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card info-card shadow-sm rounded-4 h-100">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#0d6efd"
                                        class="bi bi-wifi" viewBox="0 0 16 16">
                                        <path
                                            d="M15.384 6.115a.485.485 0 0 0-.047-.736A12.44 12.44 0 0 0 8 3C5.259 3 2.723 3.882.663 5.379a.485.485 0 0 0-.048.736.52.52 0 0 0 .668.05A11.45 11.45 0 0 1 8 4c2.507 0 4.827.802 6.716 2.164.205.148.49.13.668-.049" />
                                        <path
                                            d="M13.229 8.271a.482.482 0 0 0-.063-.745A9.46 9.46 0 0 0 8 6c-1.905 0-3.68.56-5.166 1.526a.48.48 0 0 0-.063.745.525.525 0 0 0 .652.065A8.46 8.46 0 0 1 8 7a8.46 8.46 0 0 1 4.576 1.336c.206.132.48.108.653-.065m-2.183 2.183c.226-.226.185-.605-.1-.75A6.5 6.5 0 0 0 8 9c-1.06 0-2.062.254-2.946.704-.285.145-.326.524-.1.75l.015.015c.16.16.407.19.611.09A5.5 5.5 0 0 1 8 10c.868 0 1.69.201 2.42.56.203.1.45.07.61-.091zM9.06 12.44c.196-.196.198-.52-.04-.66A2 2 0 0 0 8 11.5a2 2 0 0 0-1.02.28c-.238.14-.236.464-.04.66l.706.706a.5.5 0 0 0 .707 0l.707-.707z" />
                                    </svg>
                                </div>
                                <h5 class="mb-0">Informasi Layanan Anda</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($customer->paket)
                                <div class="card-counter bg-light rounded-3 mb-3">
                                    {{-- <div class="counter-item">
                                        <div class="counter-value">
                                            {{ $customer->paket->nama_paket ?? 'Paket Internet' }}
                                        </div>
                                        <div class="counter-label">Paket Aktif</div>
                                    </div> --}}
                                    <div class="counter-item">
                                        <div class="counter-value">
                                            {{ $customer->paket->kecepatan_paket ?? '-' }}
                                        </div>
                                        <div class="counter-label">Kecepatan</div>
                                    </div>
                                    <div class="counter-item">
                                        <div class="counter-value">
                                            {{ number_format($customer->paket->harga_paket, 0, ',', '.') }}
                                        </div>
                                        <div class="counter-label">Rp / Bulan</div>
                                    </div>
                                </div>

                                <ul>
                                    @php
                                        $latestPayment = $customer->latestPaidPayment();
                                        $displayStatusText = 'Tidak Aktif / Belum Ada Pembayaran';
                                        $displayStatusClass = 'service-status-inactive';
                                        $displayPeriodeInfo = '';
                                        $displayDurasi = '';

                                        if ($latestPayment) {
                                            $periodeMulai = \Carbon\Carbon::parse(
                                                $latestPayment->periode_tagihan_mulai,
                                            )->locale('id');
                                            $periodeSelesaiDb = \Carbon\Carbon::parse(
                                                $latestPayment->periode_tagihan_selesai,
                                            )->locale('id');
                                            $periodeSelesaiVisual = $periodeSelesaiDb->copy()->addDay();
                                            $durasiBulan = $latestPayment->durasi_pembayaran_bulan . ' bulan';

                                            if (now()->startOfDay()->lte($periodeSelesaiDb)) {
                                                $displayStatusText = 'Aktif';
                                                $displayStatusClass = 'service-status-active';
                                                $displayPeriodeInfo =
                                                    $periodeMulai->translatedFormat('d M Y') .
                                                    ' hingga ' .
                                                    $periodeSelesaiVisual->translatedFormat('d M Y');
                                                $displayDurasi = $durasiBulan;
                                            } else {
                                                $displayStatusText = 'Tidak Aktif (Masa Berlaku Habis)';
                                                $displayStatusClass = 'service-status-inactive';
                                                $displayPeriodeInfo =
                                                    'Layanan terakhir: ' .
                                                    $periodeMulai->translatedFormat('d M Y') .
                                                    ' hingga ' .
                                                    $periodeSelesaiVisual->translatedFormat('d M Y');
                                                $displayDurasi = $durasiBulan;
                                            }
                                        }
                                    @endphp

                                    <li>
                                        <span class="label">Status Layanan</span>
                                        <span class="value {{ $displayStatusClass }}">{{ $displayStatusText }}</span>
                                        @if ($displayPeriodeInfo)
                                            <div class="period-info">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                    fill="currentColor" class="bi bi-calendar3 me-1" viewBox="0 0 16 16">
                                                    <path
                                                        d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857V3.857z" />
                                                    <path
                                                        d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" />
                                                </svg>
                                                <span>Periode: {!! $displayPeriodeInfo !!}</span>
                                                @if ($displayDurasi)
                                                    <span
                                                        class="ms-1 badge bg-primary rounded-pill">{{ $displayDurasi }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </li>

                                    <li>
                                        <div class="renewal-note">
                                            <div class="d-flex align-items-start">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="#198754" class="bi bi-info-circle-fill mt-1 me-2"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
                                                </svg>
                                                <div>
                                                    <div class="fw-semibold mb-1">Catatan Perpanjangan:</div>
                                                    <p class="mb-0 small">
                                                        Kami akan mengirimkan notifikasi pengingat melalui WhatsApp
                                                        <strong>H-5 (lima hari)</strong> sebelum masa aktif layanan Anda
                                                        berakhir.
                                                        Jika Anda ingin melanjutkan layanan sebelum dari itu, silakan
                                                        gunakan tombol "Perpanjang Layanan Sekarang" pada menu navigasi atau
                                                        hubungi kami.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            @else
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#adb5bd"
                                            class="bi bi-wifi-off" viewBox="0 0 16 16">
                                            <path
                                                d="M10.706 3.294A12.545 12.545 0 0 0 8 3C5.259 3 2.723 3.882.663 5.379a.485.485 0 0 0-.048.736.518.518 0 0 0 .668.05A11.448 11.448 0 0 1 8 4c.63 0 1.249.05 1.852.148l.854-.854zM8 6c-1.905 0-3.68.56-5.166 1.526a.48.48 0 0 0-.063.745.525.525 0 0 0 .652.065 8.448 8.448 0 0 1 3.51-1.27L8 6zm2.596 1.404.785-.785c.63.24 1.227.545 1.785.907a.482.482 0 0 1 .063.745.525.525 0 0 1-.652.065 8.462 8.462 0 0 0-1.98-.932zM8 10l.933-.933a6.455 6.455 0 0 1 2.013.637c.285.145.326.524.1.75l-.015.015a.532.532 0 0 1-.611.09A5.478 5.478 0 0 0 8 10zm4.905-4.905.747-.747c.59.3 1.153.645 1.685 1.03a.485.485 0 0 1 .047.737.518.518 0 0 1-.668.05 11.493 11.493 0 0 0-1.811-1.07zM9.02 11.78c.238.14.236.464.04.66l-.707.706a.5.5 0 0 1-.707 0l-.707-.707c-.195-.195-.197-.518.04-.66A1.99 1.99 0 0 1 8 11.5c.374 0 .723.102 1.021.28zm4.355-9.905a.53.53 0 0 1 .75.75l-10.75 10.75a.53.53 0 0 1-.75-.75l10.75-10.75z" />
                                        </svg>
                                    </div>
                                    <p class="text-muted">Anda belum terdaftar pada paket layanan apapun.</p>
                                    <a href="#" class="btn btn-sm btn-primary rounded-pill mt-2">Daftar Paket
                                        Sekarang</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card info-card shadow-sm rounded-4 h-100">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#0d6efd"
                                        class="bi bi-lightning-charge-fill" viewBox="0 0 16 16">
                                        <path
                                            d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z" />
                                    </svg>
                                </div>
                                <h5 class="mb-0">Menu Navigasi</h5>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-4">
                                <h6 class="fw-semibold mb-3">Akses Cepat</h6>
                                <div class="d-grid gap-3">
                                    @if ($countUnpaidInvoices == 0)
                                        <a href="{{ route('customer.renewal.form') }}"
                                            class="btn btn-success rounded-3 action-button">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                    fill="currentColor" class="bi bi-calendar-plus" viewBox="0 0 16 16">
                                                    <path
                                                        d="M8 7a.5.5 0 0 1 .5.5V9H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7z" />
                                                    <path
                                                        d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z" />
                                                </svg>
                                                <span class="ms-2">Perpanjang Layanan Sekarang</span>
                                            </div>
                                        </a>
                                    @endif

                                    <a href="{{ route('customer.payments.index', $countUnpaidInvoices > 0 ? ['status' => 'unpaid'] : []) }}"
                                        class="btn btn-primary rounded-3 action-button position-relative">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" class="bi bi-receipt-cutoff" viewBox="0 0 16 16">
                                                <path
                                                    d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5M11.5 4a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1z" />
                                                <path
                                                    d="M2.354.646a.5.5 0 0 0-.801.13l-.5 1A.5.5 0 0 0 1 2v13H.5a.5.5 0 0 0 0 1h15a.5.5 0 0 0 0-1H15V2a.5.5 0 0 0-.053-.224l-.5-1a.5.5 0 0 0-.8-.13L13 1.293l-.646-.647a.5.5 0 0 0-.708 0L11 1.293l-.646-.647a.5.5 0 0 0-.708 0L9 1.293 8.354.646a.5.5 0 0 0-.708 0L7 1.293 6.354.646a.5.5 0 0 0-.708 0L5 1.293 4.354.646a.5.5 0 0 0-.708 0L3 1.293zm-.217 1.198.51.51a.5.5 0 0 0 .707 0L4 1.707l.646.647a.5.5 0 0 0 .708 0L6 1.707l.646.647a.5.5 0 0 0 .708 0L8 1.707l.646.647a.5.5 0 0 0 .708 0L10 1.707l.646.647a.5.5 0 0 0 .708 0L12 1.707l.646.647a.5.5 0 0 0 .708 0l.509-.51.137.274V15H2V2.118z" />
                                            </svg>
                                            <span class="ms-2">Lihat Tagihan & Pembayaran</span>
                                        </div>
                                        @if ($countUnpaidInvoices > 0)
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                {{ $countUnpaidInvoices }}
                                                <span class="visually-hidden">tagihan belum dibayar</span>
                                            </span>
                                        @endif
                                    </a>
                                    {{-- 
                                    <a href="#" class="btn btn-light rounded-3 action-button">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16">
                                                <path
                                                    d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5zm.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1h-4zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2z" />
                                            </svg>
                                            <span class="ms-2">Informasi Profil</span>
                                        </div>
                                    </a> --}}
                                </div>
                            </div>

                            <div class="mt-auto">
                                <div class="help-support p-3 bg-light rounded-3">
                                    <h6 class="fw-semibold mb-2">Butuh Bantuan?</h6>
                                    <p class="small text-muted mb-3">Jika mengalami kendala dengan layanan atau pembayaran,
                                        hubungi kami.</p>
                                    <a href="#" class="btn btn-sm btn-outline-primary rounded-pill w-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-headset me-1" viewBox="0 0 16 16">
                                            <path
                                                d="M8 1a5 5 0 0 0-5 5v1h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a6 6 0 1 1 12 0v6a2.5 2.5 0 0 1-2.5 2.5H9.366a1 1 0 0 1-.866.5h-1a1 1 0 1 1 0-2h1a1 1 0 0 1 .866.5H11.5A1.5 1.5 0 0 0 13 12h-1a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h1V6a5 5 0 0 0-5-5z" />
                                        </svg>
                                        Hubungi Kami
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
