@extends('landing.layouts.app') {{-- Menggunakan layout landing page kamu --}}

@section('title', $pageTitle ?? 'Atur Password Baru')

@push('styles')
    <style>
        {{-- Style ini sama dengan di forgot_password_otp.blade.php. Sebaiknya pindahkan ke file CSS global landing page jika dipakai di banyak tempat --}} .auth-content-area {
            padding-top: 100px;
            padding-bottom: 60px;
            min-height: 70vh;
            display: flex;
            align-items: center;
        }

        .card-auth {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.07) !important;
        }

        .card-auth .card-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border-radius: 15px 15px 0 0 !important;
            padding: 1.25rem 1.5rem;
            border-bottom: none;
        }

        .card-auth .card-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #ffffff;
        }

        .card-auth .form-label {
            font-weight: 500;
            color: #344767;
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
            background-color: #f8d7da;
            color: #842029;
        }

        .card-auth .card-footer {
            background-color: transparent;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* Untuk toggle password di input group */
        .card-auth .input-group .btn-outline-secondary {
            border-color: #e9ecef;
            color: #6c757d;
        }

        .card-auth .input-group .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            border-color: #0d6efd;
            color: #0d6efd;
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
                            <h3 class="mb-0">Atur Password Baru</h3>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted text-center small mb-4">
                                Masukkan kode OTP yang telah kami kirim ke nomor WhatsApp Anda
                                ({{ $wa_number ? '******' . substr($wa_number, -4) : '' }}), lalu atur password baru Anda.
                            </p>

                            {{-- Menampilkan pesan error validasi atau error umum --}}
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    @foreach ($errors->all() as $error)
                                        {{ $error }}<br>
                                    @endforeach
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif
                            @if (session('success'))
                                {{-- Meskipun jarang ada success di sini, tapi jaga-jaga --}}
                                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif


                            <form method="POST" action="{{ route('customer.password.otp.update') }}"
                                class="needs-validation" novalidate>
                                @csrf

                                {{-- Input tersembunyi atau disable untuk nomor WhatsApp --}}
                                {{-- Ini untuk dikirim kembali ke controller saat validasi OTP dan reset password --}}
                                <input type="hidden" name="wa_customer" value="{{ $wa_number ?? old('wa_customer') }}">


                                <div class="form-group mb-3">
                                    <label for="otp_code" class="form-label">Kode OTP</label>
                                    <input type="text" class="form-control @error('otp_code') is-invalid @enderror"
                                        id="otp_code" name="otp_code" value="{{ old('otp_code') }}"
                                        placeholder="Masukkan 6 digit OTP" required autofocus inputmode="numeric"
                                        pattern="[0-9]*" maxlength="6">
                                    @error('otp_code')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">Password Baru</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            id="password" name="password" placeholder="Masukkan Password Baru" required>
                                        <button
                                            class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                                            type="button" id="togglePasswordResetOtp" style="width: 42px;">
                                            <i class="mdi mdi-eye" id="eyeIconResetOtp"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password_confirmation"
                                            name="password_confirmation" placeholder="Ulangi Password Baru" required>
                                        <button
                                            class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                                            type="button" id="togglePasswordConfirmResetOtp" style="width: 42px;">
                                            <i class="mdi mdi-eye" id="eyeIconConfirmResetOtp"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-key me-2"></i>Atur Ulang Password
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-center py-3">
                            <a href="{{ route('customer.password.otp.request_form') }}" class="text-muted small">
                                <i class="fab fa-whatsapp me-1"></i> Tidak menerima OTP? Kirim ulang.
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{-- Menggunakan stack 'scripts' yang ada di layout landing.layouts.app --}}
    {{-- Jika MDI Font belum di-load global di layout, uncomment baris di bawah atau pindahkan ke @push('styles') --}}
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css"> --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // For password field
            const togglePasswordButton = document.getElementById('togglePasswordResetOtp');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIconResetOtp');

            if (togglePasswordButton && passwordInput && eyeIcon) {
                togglePasswordButton.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    if (type === 'password') {
                        eyeIcon.classList.remove('mdi-eye-off');
                        eyeIcon.classList.add('mdi-eye');
                    } else {
                        eyeIcon.classList.remove('mdi-eye');
                        eyeIcon.classList.add('mdi-eye-off');
                    }
                });
            }

            // For confirmation password field
            const toggleConfirmPasswordButton = document.getElementById('togglePasswordConfirmResetOtp');
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const eyeIconConfirm = document.getElementById('eyeIconConfirmResetOtp');

            if (toggleConfirmPasswordButton && confirmPasswordInput && eyeIconConfirm) {
                toggleConfirmPasswordButton.addEventListener('click', function() {
                    const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' :
                        'password';
                    confirmPasswordInput.setAttribute('type', type);

                    if (type === 'password') {
                        eyeIconConfirm.classList.remove('mdi-eye-off');
                        eyeIconConfirm.classList.add('mdi-eye');
                    } else {
                        eyeIconConfirm.classList.remove('mdi-eye');
                        eyeIconConfirm.classList.add('mdi-eye-off');
                    }
                });
            }

            // Auto-focus ke field OTP jika nomor WA sudah ada (artinya baru di-redirect dari halaman kirim OTP)
            const waNumberFromQuery = @json($wa_number); // Ambil dari PHP
            const otpCodeInput = document.getElementById('otp_code');
            if (waNumberFromQuery && otpCodeInput) {
                otpCodeInput.focus();
            }
        });
    </script>
@endpush
