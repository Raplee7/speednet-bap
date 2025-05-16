@extends('landing.layouts.app') {{-- Menggunakan layout landing page --}}

@section('title', $pageTitle ?? 'Dashboard Saya')

@push('styles')
    {{-- CSS Khusus untuk halaman dashboard pelanggan jika diperlukan --}}
    <style>
        .customer-dashboard-content {
            padding-top: 120px;
            /* Sesuaikan dengan tinggi header fixed Anda */
            padding-bottom: 60px;
            min-height: 75vh;
            /* Minimal tinggi konten agar footer tidak naik */
        }

        .welcome-banner {
            background: linear-gradient(to right, rgba(var(--bs-primary-rgb), 0.9), rgba(var(--bs-primary-rgb), 0.7)), url("{{ asset('cust-assets/img/hero-bg.webp') }}") center center;
            /* Ganti dengan gambar background yang sesuai */
            background-size: cover;
            color: white;
            border-radius: .75rem;
            /* rounded-4 */
        }

        .info-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }

        .info-card .card-header {
            background-color: var(--bs-primary-rgb, #0D6EFD);
            /* Atau warna spesifik template Anda */
            color: white;
            border-bottom: none;
        }

        .info-card .card-body ul {
            list-style: none;
            padding-left: 0;
        }

        .info-card .card-body ul li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .info-card .card-body ul li:last-child {
            border-bottom: none;
        }

        .info-card .card-body .label {
            color: #6c757d;
            /* text-muted */
            font-size: 0.9em;
        }

        .info-card .card-body .value {
            font-weight: 600;
            /* fw-semibold */
        }

        .action-button {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
    </style>
@endpush

@section('content')
    <section class="customer-dashboard-content">
        <div class="container">
            {{-- Notifikasi jika ada dari redirect (misalnya setelah login berhasil) --}}
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Banner Selamat Datang --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="p-4 p-md-5 mb-4 rounded-4 shadow-sm welcome-banner">
                        <div class="col-md-8 px-0">
                            <h1 class="display-5 fw-bold text-white fst-italic">Halo,
                                {{ Str::words($customer->nama_customer, 2, '') }}!</h1>
                            <p class="lead my-3">Selamat datang kembali di Area Pelanggan Speednet BAP. Di sini Anda dapat
                                mengelola layanan dan melihat informasi tagihan Anda.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Kolom Informasi Layanan --}}
                {{-- Ini adalah bagian dari resources/views/customer_area/dashboard.blade.php --}}
                {{-- Ganti blok <div class="col-lg-7 col-md-12">...</div> yang lama dengan ini --}}


                <div class="col-lg-7 col-md-12">
                    <div class="card shadow-sm border-0 rounded-3 h-100 info-card">
                        <div class="card-header rounded-top-3 py-3">
                            <h5 class="mb-0 fw-bold d-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor"
                                    class="bi bi-wifi me-2 text-primary" viewBox="0 0 16 16">
                                    <path
                                        d="M15.384 6.115a.485.485 0 0 0-.047-.736A12.44 12.44 0 0 0 8 3C5.259 3 2.723 3.882.663 5.379a.485.485 0 0 0-.048.736.52.52 0 0 0 .668.05A11.45 11.45 0 0 1 8 4c2.507 0 4.827.802 6.716 2.164.205.148.49.13.668-.049" />
                                    <path
                                        d="M13.229 8.271a.482.482 0 0 0-.063-.745A9.46 9.46 0 0 0 8 6c-1.905 0-3.68.56-5.166 1.526a.48.48 0 0 0-.063.745.525.525 0 0 0 .652.065A8.46 8.46 0 0 1 8 7a8.46 8.46 0 0 1 4.576 1.336c.206.132.48.108.653-.065m-2.183 2.183c.226-.226.185-.605-.1-.75A6.5 6.5 0 0 0 8 9c-1.06 0-2.062.254-2.946.704-.285.145-.326.524-.1.75l.015.015c.16.16.407.19.611.09A5.5 5.5 0 0 1 8 10c.868 0 1.69.201 2.42.56.203.1.45.07.61-.091zM9.06 12.44c.196-.196.198-.52-.04-.66A2 2 0 0 0 8 11.5a2 2 0 0 0-1.02.28c-.238.14-.236.464-.04.66l.706.706a.5.5 0 0 0 .707 0l.707-.707z" />
                                </svg>
                                Informasi Layanan Anda
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            @if ($customer->paket)
                                <ul class="mb-0">
                                    <li>
                                        <span class="label">Paket Aktif:</span>
                                        <span
                                            class="value">{{ $customer->paket->nama_paket ?? $customer->paket->kecepatan_paket }}</span>
                                    </li>
                                    <li>
                                        <span class="label">Harga Paket:</span>
                                        <span class="value">Rp
                                            {{ number_format($customer->paket->harga_paket, 0, ',', '.') }} / bulan</span>
                                    </li>

                                    @php
                                        $latestPayment = $customer->latestPaidPayment();
                                        $displayStatusText = 'Tidak Aktif / Belum Ada Pembayaran';
                                        $displayStatusClass = 'text-danger';
                                        $displayPeriodeInfo = '';
                                        $displayPeriodeClarification = '';
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
                                                $displayStatusClass = 'text-success fw-bold';
                                                $displayPeriodeInfo =
                                                    $periodeMulai->translatedFormat('d M Y') .
                                                    ' &mdash; ' .
                                                    $periodeSelesaiVisual->translatedFormat('d M Y');
                                                // $displayPeriodeClarification =
                                                //     '(Masa aktif sebenarnya hingga akhir hari ' .
                                                //     $periodeSelesaiDb->translatedFormat('d M Y') .
                                                //     ')';
                                                $displayDurasi = $durasiBulan;
                                            } else {
                                                $displayStatusText = 'Tidak Aktif (Masa Berlaku Habis)';
                                                $displayStatusClass = 'text-danger';
                                                $displayPeriodeInfo =
                                                    'Layanan terakhir: ' .
                                                    $periodeMulai->translatedFormat('d M Y') .
                                                    ' &mdash; ' .
                                                    $periodeSelesaiVisual->translatedFormat('d M Y');
                                                // $displayPeriodeClarification =
                                                //     '(Berakhir pada akhir hari ' .
                                                //     $periodeSelesaiDb->translatedFormat('d M Y') .
                                                //     ')';
                                                $displayDurasi = $durasiBulan;
                                            }
                                        }
                                    @endphp

                                    <li>
                                        <span class="label">Status Layanan:</span>
                                        <span class="value {{ $displayStatusClass }}">
                                            {{ $displayStatusText }}
                                            @if ($displayPeriodeInfo)
                                                <small class="d-block text-muted fw-normal" style="font-size: 0.85rem;">
                                                    Periode: {!! $displayPeriodeInfo !!} (@if ($displayDurasi)
                                                        {{ $displayDurasi }}
                                                    @endif)
                                                </small>
                                                @if ($displayPeriodeClarification)
                                                    <small class="d-block text-muted fw-normal" style="font-size: 0.75rem;">
                                                        {{ $displayPeriodeClarification }}
                                                    </small>
                                                @endif
                                            @endif
                                        </span>
                                    </li>

                                    {{-- PERUBAHAN DI SINI --}}
                                    <li class="mt-3"> {{-- Menambahkan margin atas dan border atas untuk pemisah --}}
                                        <span class="label d-block mb-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" class="bi bi-info-circle me-1" viewBox="0 0 16 16">
                                                <path
                                                    d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                                                <path
                                                    d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0" />
                                            </svg>
                                            Catatan Perpanjangan:
                                        </span>
                                        <span class="value text-muted" style="font-size: 0.9em; line-height: 1.6;">
                                            Kami akan mengirimkan notifikasi pengingat melalui WhatsApp <strong>H-5 (lima
                                                hari)</strong> sebelum masa aktif layanan Anda berakhir.
                                            Jika Anda ingin melanjutkan layanan, silakan tekan tombol "Perpanjang Layanan
                                            Sekarang" di samping atau silahkan hubungi
                                            kami.
                                        </span>
                                    </li>
                                    {{-- AKHIR PERUBAHAN --}}

                                </ul>
                            @else
                                <p class="text-muted mb-0">Anda belum terdaftar pada paket layanan apapun.</p>
                            @endif
                        </div>
                    </div>
                </div>


                {{-- Kolom Aksi Cepat --}}
                <div class="col-lg-5 col-md-12">
                    <div class="card shadow-sm border-0 rounded-3 h-100 info-card">
                        <div class="card-header rounded-top-3 py-3">
                            <h5 class="mb-0 fw-bold d-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor"
                                    class="bi bi-lightning-charge-fill me-2" viewBox="0 0 16 16">
                                    <path
                                        d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z" />
                                </svg>
                                Menu Navigasi
                            </h5>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <p class="text-muted small">Akses cepat ke halaman pengelolaan layanan Anda:</p>
                            <div class="d-grid gap-2 mt-auto"> {{-- mt-auto untuk mendorong tombol ke bawah jika card lebih tinggi --}}
                                <a href="{{ route('customer.renewal.form') }}"
                                    class="btn btn-success rounded-pill fw-semibold py-2 action-button"">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-calendar-plus-fill me-1" viewBox="0 0 16 16">
                                        <path
                                            d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4V.5zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2zM8.5 8.5V10H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V11H6a.5.5 0 0 1 0-1h1.5V8.5a.5.5 0 0 1 1 0z" />
                                    </svg>
                                    Perpanjang Layanan Sekarang
                                </a>
                                <a href="{{ route('customer.payments.index') }}"
                                    class="btn btn-primary rounded-pill fw-semibold py-2 action-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-receipt-cutoff me-2" viewBox="0 0 16 16">
                                        <path
                                            d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5M11.5 4a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1z" />
                                        <path
                                            d="M2.354.646a.5.5 0 0 0-.801.13l-.5 1A.5.5 0 0 0 1 2v13H.5a.5.5 0 0 0 0 1h15a.5.5 0 0 0 0-1H15V2a.5.5 0 0 0-.053-.224l-.5-1a.5.5 0 0 0-.8-.13L13 1.293l-.646-.647a.5.5 0 0 0-.708 0L11 1.293l-.646-.647a.5.5 0 0 0-.708 0L9 1.293 8.354.646a.5.5 0 0 0-.708 0L7 1.293 6.354.646a.5.5 0 0 0-.708 0L5 1.293 4.354.646a.5.5 0 0 0-.708 0L3 1.293zm-.217 1.198.51.51a.5.5 0 0 0 .707 0L4 1.707l.646.647a.5.5 0 0 0 .708 0L6 1.707l.646.647a.5.5 0 0 0 .708 0L8 1.707l.646.647a.5.5 0 0 0 .708 0L10 1.707l.646.647a.5.5 0 0 0 .708 0L12 1.707l.646.647a.5.5 0 0 0 .708 0l.509-.51.137.274V15H2V2.118z" />
                                    </svg>
                                    Lihat Tagihan & Pembayaran
                                </a>
                                {{-- Tombol Logout bisa juga diletakkan di sini jika tidak di header --}}
                                {{-- <form action="{{ route('customer.logout') }}" method="POST" class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger rounded-pill fw-semibold py-2 mt-2 action-button">Logout</button>
                            </form> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{-- Script khusus untuk dashboard pelanggan jika ada --}}
    <script>
        // Contoh: console.log("Dashboard Pelanggan Dimuat!");
    </script>
@endpush
