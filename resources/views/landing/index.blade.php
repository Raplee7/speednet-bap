@extends('landing.layouts.app')

@section('title', 'Beranda - Speednet')

@section('content')
    <!-- Hero Section -->
    <section id="hero" class="hero section">

        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center">
                    <h1 data-aos="fade-up">Nikmati Layanan Internet Cepat dengan Pembayaran Lebih Mudah</h1>
                    <p data-aos="fade-up" data-aos-delay="100">Akses Mudah, Pembayaran Cepat, Layanan Terjamin</p>
                </div>
                <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-out">
                    <img src="{{ asset('cust-assets/img/speednet-hero.png') }}" class="img-fluid animated" alt="">
                </div>
            </div>
        </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="tentang" class="about section">

        <div class="container" data-aos="fade-up">
            <div class="row gx-0">

                <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="content">
                        <h3>Who We Are</h3>
                        <h2>Kami adalah penyedia layanan Wi-Fi lokal yang fokus menyediakan koneksi internet cepat, stabil, dan terjangkau. Dengan sistem manajemen pembayaran berbasis web, kami memudahkan pelanggan dalam mengelola layanan dan pembayaran secara praktis dan efisien.</h2>
                        
                        <div class="text-center text-lg-start">
                            <a href="#"
                                class="btn-read-more d-inline-flex align-items-center justify-content-center align-self-center">
                                <span>Read More</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="200">
                    <img src="{{ asset('cust-assets/img/about.jpg') }}" class="img-fluid" alt="">
                </div>

            </div>
        </div>

    </section><!-- /About Section -->
    <!-- Paket Section -->
    <section id="paket" class="pricing section">

        <div class="container section-title" data-aos="fade-up">
            <h2>Paket</h2>
            <p>Pilih Paket Kecepatan Internet Terbaik untuk Kebutuhan Anda<br></p>
        </div>

        <div class="container">
            <div class="row gy-4">

                <!-- Paket 10 Mbps -->
                <div class="col-lg-6 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                    <div class="pricing-tem h-100 d-flex flex-column">
                        <h3 style="color: #20c997;">10 Mbps</h3>
                        <div class="price"><sup>Rp</sup>185.000<span> / bulan</span></div>
                        <div class="icon my-3">
                            <i class="bi bi-wifi" style="color: #20c997; font-size: 3rem;"></i>
                        </div>
                        <ul class="flex-grow-1">
                            <li>Kecepatan hingga 10 Mbps</li>
                            <li>Gratis Biaya Pemasangan*</li>
                            <li>Support 24/7</li>
                        </ul>
                        <div class="mt-auto">
                            <a href="#form-pemasangan" class="btn-buy" onclick="pilihPaket('paket10')">Pilih Paket Ini</a>
                        </div>
                    </div>
                </div>

                <!-- Paket 20 Mbps -->
                <div class="col-lg-6 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                    <div class="pricing-tem h-100 d-flex flex-column">
                        <h3 style="color: #0dcaf0;">20 Mbps</h3>
                        <div class="price"><sup>Rp</sup>250.000<span> / bulan</span></div>
                        <div class="icon my-3">
                            <i class="bi bi-wifi" style="color: #0dcaf0; font-size: 3rem;"></i>
                        </div>
                        <ul class="flex-grow-1">
                            <li>Kecepatan hingga 20 Mbps</li>
                            <li>Gratis Biaya Pemasangan*</li>
                            <li>Support 24/7</li>
                        </ul>
                        <div class="mt-auto">
                            <a href="#form-pemasangan" class="btn-buy" onclick="pilihPaket('paket20')">Pilih Paket Ini</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </section><!-- /Paket Section -->

    <!-- Formulir Section -->
    <section id="form-pemasangan" class="contact section">

        <div class="container section-title" data-aos="fade-up">
            <h2>Formulir</h2>
            <p>Formulir Pemasangan Baru</p>
        </div>
        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0 rounded-4">
                        <div class="card-body p-4 p-md-5">
                            <form action="{{ route('form.pemasangan.store') }}" method="POST" class="needs-validation"
                                novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <div class="col-md-12">
                                        <label for="nama_customer" class="form-label fs-6 fw-medium">Nama Lengkap <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text bg-light border-end-0"><i
                                                    class="bi bi-person"></i></span>
                                            <input type="text" id="nama_customer" name="nama_customer"
                                                class="form-control form-control-lg border-start-0 @error('nama_customer') is-invalid @enderror"
                                                required value="{{ old('nama_customer') }}">
                                            @error('nama_customer')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @else
                                                <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="alamat_customer" class="form-label fs-6 fw-medium">Alamat Lengkap
                                            Pemasangan <span class="text-danger">*</span></label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text bg-light border-end-0"><i
                                                    class="bi bi-geo-alt-fill"></i></span>
                                            <textarea id="alamat_customer" name="alamat_customer"
                                                class="form-control form-control-lg border-start-0 @error('alamat_customer') is-invalid @enderror" rows="3"
                                                required>{{ old('alamat_customer') }}</textarea>
                                            @error('alamat_customer')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @else
                                                <div class="invalid-feedback">Alamat lengkap wajib diisi.</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="wa_customer" class="form-label fs-6 fw-medium">Nomor WhatsApp<span
                                                class="text-danger">*</span></label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text bg-light border-end-0"><i
                                                    class="bi bi-whatsapp"></i></span>
                                            <input type="tel" id="wa_customer" name="wa_customer"
                                                class="form-control form-control-lg border-start-0">
                                        </div>
                                    </div>

                                    {{-- Dropdown paket di dalam form --}}
                                    <div class="col-md-12" id="form_paket_selection_wrapper">
                                        <label for="form_paket_id" class="form-label fs-6 fw-medium">Paket Dipilih <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text bg-light border-end-0"><i
                                                    class="bi bi-wifi"></i></span>
                                            {{-- Mengubah name menjadi "paket_id" --}}
                                            <select name="paket_id" id="form_paket_id"
                                                class="form-select form-select-lg border-start-0 @error('paket_id') is-invalid @enderror"
                                                required>
                                                <option value="" disabled {{ old('paket_id') ? '' : 'selected' }}>--
                                                    Pilih Paket Layanan --</option>
                                                @foreach ($pakets as $paket_option)
                                                    {{-- Menggunakan variabel berbeda untuk loop agar tidak konflik --}}
                                                    <option value="{{ $paket_option->id_paket }}"
                                                        {{ old('paket_id') == $paket_option->id_paket ? 'selected' : '' }}>
                                                        {{ $paket_option->kecepatan_paket }} -
                                                        Rp{{ number_format($paket_option->harga_paket, 0, ',', '.') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('paket_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @else
                                                <div class="invalid-feedback">Anda harus memilih paket layanan.</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12 text-center mt-5">
                                        <button type="submit"
                                            class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                fill="currentColor" class="bi bi-send-fill me-2" viewBox="0 0 16 16">
                                                <path
                                                    d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z" />
                                            </svg>
                                            Kirim Pengajuan Pemasangan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Faq Section -->
    <section id="faq" class="faq section">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>F.A.Q</h2>
            <p>Frequently Asked Questions</p>
        </div><!-- End Section Title -->

        <div class="container">

            <div class="row">

                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">

                    <div class="faq-container">

                        <div class="faq-item faq-active">
                            <h3>Layanan internet tersedia di daerah mana saja?</h3>
                            <div class="faq-content">
                                <p>Layanan kami saat ini tersedia di wilayah Desa Parit Baru dan sekitarnya. Untuk info cakupan wilayah terbaru, silakan hubungi admin.</p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div><!-- End Faq item-->

                        <div class="faq-item">
                            <h3>Apa keunggulan layanan Wi-Fi ini dibandingkan penyedia lain?</h3>
                            <div class="faq-content">
                                <p>Kami menawarkan kecepatan stabil, harga terjangkau, pemasangan gratis*, dan layanan pelanggan yang responsif 24/7.</p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div><!-- End Faq item-->

                        <div class="faq-item">
                            <h3>Apakah ada biaya pemasangan?</h3>
                            <div class="faq-content">
                                <p>Tidak, kami memberikan gratis biaya pemasangan untuk pelanggan baru sesuai ketentuan yang berlaku.
                                </p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div><!-- End Faq item-->

                    </div>

                </div><!-- End Faq Column-->

                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">

                    <div class="faq-container">

                        <div class="faq-item">
                            <h3>Apakah tersedia layanan bantuan teknis?</h3>
                            <div class="faq-content">
                                <p>Ya, kami menyediakan layanan bantuan teknis pada jam kerja, yaitu Senin hingga Sabtu, pukul 08.00 – 17.00 WIB, melalui WhatsApp, telepon, atau langsung dari dashboard pelanggan.</p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div><!-- End Faq item-->

                        <div class="faq-item">
                            <h3>Apa yang harus saya lakukan jika koneksi internet saya bermasalah?</h3>
                            <div class="faq-content">
                                <p>Segera hubungi admin agar tim teknis kami dapat segera membantu.
                                </p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div><!-- End Faq item-->

                        <div class="faq-item">
                            <h3>Bagaimana jika saya terlambat membayar tagihan?</h3>
                            <div class="faq-content">
                                <p>Kami memberikan masa tenggang tertentu. Namun jika melebihi batas waktu, layanan akan dihentikan sementara hingga pembayaran dilakukan.</p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div><!-- End Faq item-->

                    </div>

                </div><!-- End Faq Column-->

            </div>

        </div>

    </section><!-- /Faq Section -->

    <!-- Contact Section -->
    <section id="contact" class="contact section">

        <div class="container section-title" data-aos="fade-up">
            <h2>Kontak</h2>
            <p>Hubungi Kami</p> {{-- Sedikit modifikasi teks --}}
        </div>
        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="row gy-4">

                <div class="col-lg-6"> {{-- Kolom untuk Info Kontak (Alamat, Telepon, Email, Jam Buka) --}}
                    <div class="row gy-4">
                        <div class="col-md-6">
                            <div class="info-item h-100" data-aos="fade" data-aos-delay="200"> {{-- Tambah h-100 jika ingin sama tinggi --}}
                                <i class="bi bi-geo-alt"></i>
                                <h3>Alamat</h3>
                                <p>Jl. Arteri Supadio Jl. Pd. Indah Lestari No.12B, Sungai Raya, Kec. Sungai Raya, Kabupaten Kubu Raya,</p> {{-- GANTI DENGAN ALAMATMU --}}
                                <p>Pontianak, Kalimantan Barat</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item h-100" data-aos="fade" data-aos-delay="300">
                                <i class="bi bi-telephone"></i>
                                <h3>Telepon Kami</h3>
                                <p>+62 897 0002 025</p> {{-- GANTI DENGAN NOMORMU --}}
                             
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item h-100" data-aos="fade" data-aos-delay="400">
                                <i class="bi bi-envelope"></i>
                                <h3>Email Kami</h3>
                                <p>info@speednetbap.test</p> {{-- GANTI DENGAN EMAILMU --}}
                                <p>dukungan@speednetbap.test</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item h-100" data-aos="fade" data-aos-delay="500">
                                <i class="bi bi-clock"></i>
                                <h3>Jam Buka</h3>
                                <p>Senin - Sabtu</p>
                                <p>08:00 - 17:00 WIB</p> {{-- Sesuaikan jam buka --}}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom untuk Peta Google Maps --}}
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d844.4518182848843!2d109.37395865009101!3d-0.08544102025188481!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e1d5b28610226db%3A0xb7ddb1324f5bf648!2sYayasan%20Karsa%20Cipta%20Mandiri%20Indonesia!5e0!3m2!1sen!2sid!4v1747985914999!5m2!1sen!2sid"
                        width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>

    </section><!-- /Contact Section -->
@endsection

@push('scripts')
    <script>
        // Fungsi untuk menangani pemilihan paket
        function pilihPaket(paketId) { // Hanya perlu paketId sekarang
            const formPaketSelectionDropdown = document.getElementById('form_paket_id'); // Dropdown di form

            if (formPaketSelectionDropdown) {
                formPaketSelectionDropdown.value = paketId; // Otomatis pilih paket di dropdown form
            }

            // Scroll ke formulir
            const formulirSection = document.getElementById('form-pemasangan');
            if (formulirSection) {
                formulirSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                }); // block: 'start' agar bagian atas form terlihat
            }
        }

        // Untuk validasi Bootstrap
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
@endpush
