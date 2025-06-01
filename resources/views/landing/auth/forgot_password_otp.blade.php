@extends('landing.layouts.app') {{-- Menggunakan layout landing page kamu --}}

@section('title', $pageTitle ?? 'Lupa Password - Kirim OTP')

@push('styles')
    <style>
        {{-- Kamu bisa tambahkan style spesifik untuk halaman ini jika perlu --}} {{-- Contoh style dari halaman ubah password customer yang kamu buat sebelumnya, bisa disesuaikan --}} .auth-content-area {
            /* Ganti nama kelas ini jika berbeda dari customer-content-area */
            padding-top: 100px;
            /* Sesuaikan padding atas agar pas dengan header landing page-mu */
            padding-bottom: 60px;
            min-height: 70vh;
            /* Sesuaikan min-height */
            display: flex;
            align-items: center;
        }

        .card-auth {
            border: none;
            border-radius: 15px;
            /* Rounded corner seperti di referensi card-mu */
            box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.07) !important;
            /* Shadow lebih halus */
        }

        .card-auth .card-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            /* Contoh gradient biru */
            border-radius: 15px 15px 0 0 !important;
            padding: 1.25rem 1.5rem;
            border-bottom: none;
        }

        .card-auth .card-header h3 {
            font-size: 1.5rem;
            /* Sesuaikan ukuran judul */
            font-weight: 600;
            color: #ffffff;
        }

        .card-auth .form-label {
            font-weight: 500;
            color: #344767;
            /* Warna label dari CSS ubah password customer-mu */
        }

        .card-auth .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #e9ecef;
        }

        .card-auth .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
        }

        .card-auth .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }

        .card-auth .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2);
        }

        .card-auth .alert {
            border-radius: 10px;
            border: none;
        }

        .card-auth .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .card-auth .alert-danger,
        .card-auth .alert-warning {
            /* Gabungkan style untuk error dan warning jika mirip */
            background-color: #f8d7da;
            color: #842029;
        }

        .card-auth .card-footer {
            background-color: transparent;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
    </style>
@endpush

@section('content')
    <section class="auth-content-area">
        <div class="container" data-aos="fade-up">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7 col-sm-9">
                    <div class="card card-auth">
                        <div class="card-header text-center">
                            <h3 class="mb-0">Lupa Password?</h3>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted text-center small mb-4">
                                Masukkan nomor WhatsApp Anda yang terdaftar. Kami akan mengirimkan kode OTP untuk mereset
                                password Anda.
                            </p>

                            {{-- Menampilkan pesan sukses (misalnya OTP berhasil dikirim) --}}
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            {{-- Menampilkan pesan error validasi atau error umum --}}
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    @if ($errors->has('wa_customer'))
                                        {{ $errors->first('wa_customer') }}
                                    @else
                                        Terjadi kesalahan. Silakan coba lagi.
                                    @endif
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif


                            <form method="POST" action="{{ route('customer.password.otp.send') }}" class="needs-validation"
                                novalidate>
                                @csrf

                                <div class="form-group mb-3">
                                    <label for="wa_customer" class="form-label">Nomor WhatsApp Terdaftar</label>
                                    <input type="text" class="form-control @error('wa_customer') is-invalid @enderror"
                                        id="wa_customer" name="wa_customer" value="{{ old('wa_customer') }}"required
                                        autofocus>
                                    @error('wa_customer')
                                        {{-- Pesan error spesifik sudah ditampilkan di alert di atas --}}
                                    @enderror
                                </div>

                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fab fa-whatsapp me-2"></i>Kirim Kode OTP
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-center py-3">
                            <a href="{{ route('customer.login.form') }}" class="text-muted small"> {{-- Sesuaikan route login pelangganmu --}}
                                <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
