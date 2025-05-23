@extends('landing.layouts.app') {{-- Menggunakan layout landing yang baru --}}

@section('title', 'Beranda - Speednet') {{-- Contoh judul spesifik --}}

@section('content')
    <!-- Hero Section -->
    <section id="hero" class="hero section">

        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center">
                    <h1 data-aos="fade-up">We offer modern solutions for growing your business</h1>
                    <p data-aos="fade-up" data-aos-delay="100">We are team of talented designers making websites
                        with Bootstrap</p>
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
                        <h2>Expedita voluptas omnis cupiditate totam eveniet nobis sint iste. Dolores est repellat
                            corrupti reprehenderit.</h2>
                        <p>
                            Quisquam vel ut sint cum eos hic dolores aperiam. Sed deserunt et. Inventore et et dolor
                            consequatur itaque ut voluptate sed et. Magnam nam ipsum tenetur suscipit voluptatum nam
                            et est corrupti.
                        </p>
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

                @php
                    $paketIconColors = ['#20c997', '#0dcaf0', '#fd7e14', '#0d6efd', '#6f42c1', '#ffc107'];
                @endphp

                @forelse ($pakets as $index => $paket)
                    <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="{{ ($index + 1) * 100 }}">
                        <div class="pricing-tem h-100 d-flex flex-column">
                            @php
                                $iconColor = $paketIconColors[$index % count($paketIconColors)];
                            @endphp

                            <h3 style="color: {{ $iconColor }};">{{ $paket->kecepatan_paket }}</h3>

                            <div class="price"><sup>Rp</sup>{{ number_format($paket->harga_paket, 0, ',', '.') }}<span> /
                                    bulan</span></div>

                            <div class="icon my-3">
                                <i class="bi bi-wifi" style="color: {{ $iconColor }}; font-size: 3rem;"></i>
                            </div>

                            <ul class="flex-grow-1">
                                <li>Kecepatan hingga {{ $paket->kecepatan_paket }}</li>
                                <li>Unlimited Kuota</li>
                                <li>Gratis Biaya Pemasangan*</li>
                                <li>Support 24/7</li>
                            </ul>

                            <div class="mt-auto">
                                {{-- Tombol memanggil fungsi JavaScript untuk memilih paket di form dan scroll --}}
                                <a href="#form-pemasangan" class="btn-buy"
                                    onclick="pilihPaket('{{ $paket->id_paket }}')">Pilih Paket Ini</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-center">Belum ada paket yang tersedia saat ini. Silakan kembali lagi nanti.</p>
                    </div>
                @endforelse

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
                            <h3>Non consectetur a erat nam at lectus urna duis?</h3>
                            <div class="faq-content">
                                <p>Feugiat pretium nibh ipsum consequat. Tempus iaculis urna id volutpat lacus
                                    laoreet non curabitur gravida. Venenatis lectus magna fringilla urna porttitor
                                    rhoncus dolor purus non.</p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div><!-- End Faq item-->

                        <div class="faq-item">
                            <h3>Feugiat scelerisque varius morbi enim nunc faucibus a pellentesque?</h3>
                            <div class="faq-content">
                                <p>Dolor sit amet consectetur adipiscing elit pellentesque habitant morbi. Id
                                    interdum velit laoreet id donec ultrices. Fringilla phasellus faucibus
                                    scelerisque eleifend donec pretium. Est pellentesque elit ullamcorper dignissim.
                                    Mauris ultrices eros in cursus turpis massa tincidunt dui.</p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div><!-- End Faq item-->

                        <div class="faq-item">
                            <h3>Dolor sit amet consectetur adipiscing elit pellentesque?</h3>
                            <div class="faq-content">
                                <p>Eleifend mi in nulla posuere sollicitudin aliquam ultrices sagittis orci.
                                    Faucibus pulvinar elementum integer enim. Sem nulla pharetra diam sit amet nisl
                                    suscipit. Rutrum tellus pellentesque eu tincidunt. Lectus urna duis convallis
                                    convallis tellus. Urna molestie at elementum eu facilisis sed odio morbi quis
                                </p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div><!-- End Faq item-->

                    </div>

                </div><!-- End Faq Column-->

                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">

                    <div class="faq-container">

                        <div class="faq-item">
                            <h3>Ac odio tempor orci dapibus. Aliquam eleifend mi in nulla?</h3>
                            <div class="faq-content">
                                <p>Dolor sit amet consectetur adipiscing elit pellentesque habitant morbi. Id
                                    interdum velit laoreet id donec ultrices. Fringilla phasellus faucibus
                                    scelerisque eleifend donec pretium. Est pellentesque elit ullamcorper dignissim.
                                    Mauris ultrices eros in cursus turpis massa tincidunt dui.</p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div><!-- End Faq item-->

                        <div class="faq-item">
                            <h3>Tempus quam pellentesque nec nam aliquam sem et tortor consequat?</h3>
                            <div class="faq-content">
                                <p>Molestie a iaculis at erat pellentesque adipiscing commodo. Dignissim suspendisse
                                    in est ante in. Nunc vel risus commodo viverra maecenas accumsan. Sit amet nisl
                                    suscipit adipiscing bibendum est. Purus gravida quis blandit turpis cursus in
                                </p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div><!-- End Faq item-->

                        <div class="faq-item">
                            <h3>Perspiciatis quod quo quos nulla quo illum ullam?</h3>
                            <div class="faq-content">
                                <p>Enim ea facilis quaerat voluptas quidem et dolorem. Quis et consequatur non sed
                                    in suscipit sequi. Distinctio ipsam dolore et.</p>
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
                                <p>Jl. Contoh Alamat No. 123</p> {{-- GANTI DENGAN ALAMATMU --}}
                                <p>Pontianak, Kalimantan Barat</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item h-100" data-aos="fade" data-aos-delay="300">
                                <i class="bi bi-telephone"></i>
                                <h3>Telepon Kami</h3>
                                <p>+62 812 3456 7890</p> {{-- GANTI DENGAN NOMORMU --}}
                                <p>+62 561 123 456</p>
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
                                <p>Senin - Jumat</p>
                                <p>08:00 - 16:00 WIB</p> {{-- Sesuaikan jam buka --}}
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
