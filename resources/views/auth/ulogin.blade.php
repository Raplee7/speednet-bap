@extends('layouts.auth_layout') {{-- Menggunakan layout dasar yang baru kita buat --}}

@section('title', 'Login') {{-- Mengisi @yield('title') di layout --}}

@section('content_auth') {{-- Mengisi @yield('content_auth') di layout --}}
    <h1 class="mb-2 text-center">LOGIN</h1>
    <p class="mb-5 text-center">Login untuk mengatur pembayaran Wi-Fi</p>
    <form method="POST" action="{{ route('login') }}"> {{-- Pastikan action ke route('login') yg benar --}}
        @csrf
        @error('email')
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <span>{{ $message }}</span> {{-- Pesan error dari validasi email atau password salah --}}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @enderror
        {{-- Jika ada error kredensial umum dari AuthController --}}
        @if (session('error_login'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <span>{{ session('error_login') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email', 'login') is-invalid @enderror"
                id="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-group has-validation">
                <input type="password" name="password" class="form-control @error('password', 'login') is-invalid @enderror"
                    id="password" required>
                <span class="input-group-text" id="togglePassword"
                    style="cursor: pointer; border-left: none; background-color: transparent;">
                    <i class="fas fa-eye-slash" id="eyeIcon"></i>
                </span>
                @error('password', 'login')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                        {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        Ingat Saya
                    </label>
                </div>
            </div>
            {{-- Link Lupa Password akan kita letakkan di sini nanti --}}
            @if (Route::has('password.request'))
                <div class="col-md-6 d-flex justify-content-end align-items-center px-0">
                    <a class="btn btn-link" href="{{ route('password.request') }}">
                        Lupa Password?
                    </a>
                </div>
            @endif
        </div>


        <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const togglePasswordButton = document.getElementById('togglePassword');
                const passwordInput = document.getElementById('password');
                const eyeIcon = document.getElementById('eyeIcon');

                if (togglePasswordButton && passwordInput && eyeIcon) {
                    togglePasswordButton.addEventListener('click', function() {
                        // Toggle tipe input password
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);

                        // Toggle ikon mata
                        if (type === 'password') {
                            eyeIcon.classList.remove('fa-eye');
                            eyeIcon.classList.add('fa-eye-slash');
                        } else {
                            eyeIcon.classList.remove('fa-eye-slash');
                            eyeIcon.classList.add('fa-eye');
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
