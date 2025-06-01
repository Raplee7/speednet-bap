@extends('landing.layouts.app')

@section('title', $pageTitle ?? 'Ubah Password')

@push('styles')
    <style>
        .customer-content-area {
            padding-top: 120px;
            padding-bottom: 60px;
            min-height: 75vh;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05) !important;
        }

        .card-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }

        .card-header h4 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .form-label {
            font-weight: 500;
            color: #344767;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
        }

        .input-group .btn {
            padding: 0.75rem;
            border-radius: 0 8px 8px 0;
        }

        .input-group .btn-outline-secondary {
            border: 1px solid #e9ecef;
            border-left: none;
            background-color: white;
            color: #6c757d;
            padding: 0.75rem;
            transition: all 0.2s ease;
        }

        .input-group .btn-outline-secondary:hover,
        .input-group .btn-outline-secondary:focus {
            background-color: #f8f9fa;
            border-color: #0d6efd;
            color: #0d6efd;
            box-shadow: none;
        }

        .input-group .form-control {
            border-right: none;
        }

        .input-group .form-control:focus + .btn-outline-secondary {
            border-color: #0d6efd;
        }

        .input-group .fa {
            font-size: 1rem;
            line-height: 1;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
        }

        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .card-footer {
            background-color: transparent;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-footer a {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .card-footer a:hover {
            color: #0d6efd;
        }

        /* Password strength indicator */
        .password-strength-meter {
            height: 4px;
            background-color: #eee;
            border-radius: 2px;
            margin-top: 0.5rem;
        }

        .password-strength-meter div {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        .strength-weak {
            background-color: #dc3545;
            width: 25%;
        }

        .strength-fair {
            background-color: #ffc107;
            width: 50%;
        }

        .strength-good {
            background-color: #0dcaf0;
            width: 75%;
        }

        .strength-strong {
            background-color: #198754;
            width: 100%;
        }
    </style>
@endpush

@section('content')
    <section class="customer-content-area">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h4 class="mb-0 text-white">
                                <i class="fas fa-lock me-2"></i>{{ $pageTitle ?? 'Ubah Password Akun Anda' }}
                            </h4>
                        </div>

                        <div class="card-body p-4">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i><strong>Oops!</strong> Terjadi kesalahan:
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('customer.account.password.update') }}"
                                class="needs-validation" novalidate>
                                @csrf

                                <!-- Replace the password input fields with these -->
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Password Saat Ini <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password"
                                            class="form-control @error('current_password') is-invalid @enderror"
                                            id="current_password" name="current_password" required>
                                        <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                                            type="button" id="toggleCurrentPassword" aria-label="Toggle password visibility">
                                            <i class="fa fa-eye" id="eyeIconCurrent"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Password Baru <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password"
                                            class="form-control @error('new_password') is-invalid @enderror"
                                            id="new_password_customer" name="new_password" required>
                                        <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                                            type="button" id="toggleNewPasswordCustomer" aria-label="Toggle password visibility">
                                            <i class="fa fa-eye" id="eyeIconNewCustomer"></i>
                                        </button>
                                    </div>
                                    @error('new_password')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control"
                                            id="new_password_confirmation_customer"
                                            name="new_password_confirmation" required>
                                        <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                                            type="button" id="toggleConfirmPassword" aria-label="Toggle password visibility">
                                            <i class="fa fa-eye" id="eyeIconConfirm"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Ubah Password Saya
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="card-footer text-center py-3">
                            <a href="{{ route('customer.dashboard') }}" class="text-muted">
                                <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard Pelanggan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to setup password toggle
            function setupPasswordToggle(buttonId, inputId, iconId) {
                const toggleButton = document.getElementById(buttonId);
                const passwordInput = document.getElementById(inputId);
                const eyeIcon = document.getElementById(iconId);

                if (toggleButton && passwordInput && eyeIcon) {
                    toggleButton.addEventListener('click', function() {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);

                        if (type === 'password') {
                            eyeIcon.classList.remove('fa-eye-slash');
                            eyeIcon.classList.add('fa-eye');
                        } else {
                            eyeIcon.classList.remove('fa-eye');
                            eyeIcon.classList.add('fa-eye-slash');
                        }
                    });
                }
            }

            // Setup toggle for all password fields
            setupPasswordToggle('toggleCurrentPassword', 'current_password', 'eyeIconCurrent');
            setupPasswordToggle('toggleNewPasswordCustomer', 'new_password_customer', 'eyeIconNewCustomer');
            setupPasswordToggle('toggleConfirmPassword', 'new_password_confirmation_customer', 'eyeIconConfirm');

            // Password strength meter code (existing)
            const passwordInput = document.getElementById('new_password_customer');
            const strengthMeter = document.createElement('div');
            strengthMeter.className = 'password-strength-meter';
            strengthMeter.innerHTML = '<div></div>';
            passwordInput.parentNode.appendChild(strengthMeter);

            passwordInput.addEventListener('input', function() {
                const strength = calculatePasswordStrength(this.value);
                const meterBar = strengthMeter.querySelector('div');

                meterBar.className = '';
                if (strength > 75) meterBar.classList.add('strength-strong');
                else if (strength > 50) meterBar.classList.add('strength-good');
                else if (strength > 25) meterBar.classList.add('strength-fair');
                else meterBar.classList.add('strength-weak');
            });

            function calculatePasswordStrength(password) {
                let strength = 0;
                if (password.length >= 8) strength += 25;
                if (password.match(/[A-Z]/)) strength += 25;
                if (password.match(/[0-9]/)) strength += 25;
                if (password.match(/[^A-Za-z0-9]/)) strength += 25;
                return strength;
            }
        });
    </script>
@endpush
