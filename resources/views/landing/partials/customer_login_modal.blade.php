<div class="modal fade" id="customerLoginModal" tabindex="-1" aria-labelledby="customerLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <!-- Header dengan gradient -->
            <div class="modal-header border-0 text-white rounded-top-4 bg-primary">
                <h5 class="modal-title fw-bold text-white" id="customerLoginModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor"
                        class="bi bi-person-circle me-2 align-middle" viewBox="0 0 16 16">
                        <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                        <path fill-rule="evenodd"
                            d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
                    </svg>
                    Login Pelanggan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <!-- Body dengan padding lebih baik -->
            <div class="modal-body p-4 p-lg-5">
                <!-- Pesan selamat datang -->
                <div class="text-center mb-4">
                    <h6 class="fw-normal text-secondary">Selamat datang!</h6>
                    <p class="small text-muted">Silakan masukkan kredensial Anda untuk masuk ke akun</p>
                </div>

                <form method="POST" action="{{ route('customer.login.attempt') }}" id="customerLoginFormInsideModal">
                    @csrf

                    <!-- Alert pesan error dengan animasi dan styling lebih baik -->
                    @if ($errors->customer_login->any())
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 border-start border-danger border-4"
                            role="alert">
                            <div class="d-flex">
                                <div class="me-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        fill="currentColor" class="bi bi-exclamation-circle text-danger"
                                        viewBox="0 0 16 16">
                                        <path
                                            d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                        <path
                                            d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z" />
                                    </svg>
                                </div>
                                <div>
                                    <ul class="mb-0 ps-0" style="list-style-type: none;">
                                        @foreach ($errors->customer_login->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Form input dengan ikon -->
                    <div class="mb-4">
                        <label for="modal_active_user_input" class="form-label fw-semibold">Active User</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-person text-secondary"></i>
                            </span>
                            <input id="modal_active_user_input" type="text"
                                class="form-control bg-light border-start-0 @error('active_user_modal', 'customer_login') is-invalid @enderror"
                                name="active_user_modal" value="{{ old('active_user_modal') }}" required
                                autocomplete="username" autofocus placeholder="Masukkan Active User Anda">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="modal_password_input" class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-lock text-secondary"></i>
                            </span>
                            <input id="modal_password_input" type="password"
                                class="form-control bg-light border-start-0 border-end-0 @error('password_modal', 'customer_login') is-invalid @enderror"
                                name="password_modal" required autocomplete="current-password"
                                placeholder="Masukkan password">
                            <button class="input-group-text bg-light border-start-0" type="button" id="togglePassword">
                                <i class="bi bi-eye-slash text-secondary"></i>
                            </button>
                        </div>
                    </div>


                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember_modal"
                                id="modal_remember_customer_input" {{ old('remember_modal') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="modal_remember_customer_input">
                                Ingat Saya
                            </label>
                        </div>
                        <a href="{{ route('customer.password.otp.request_form') }}"
                            class="text-decoration-none small">Lupa
                            Password?</a>
                    </div>

                    <!-- Tombol login yang lebih menarik dengan efek hover -->
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn bg-primary btn-primary btn-lg rounded-pill fw-semibold py-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-box-arrow-in-right me-1" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z" />
                                <path fill-rule="evenodd"
                                    d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z" />
                            </svg>
                            Login
                        </button>
                    </div>
                </form>

                <!-- Divider dengan text -->
                <div class="position-relative my-4">
                    <hr>
                    <div class="position-absolute top-50 start-50 translate-middle px-3 bg-white text-muted small">
                        atau
                    </div>
                </div>

                <!-- Pesan bantuan -->
                <div class="text-center">
                    <div class="mb-3">
                        <span class="badge bg-light text-dark p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-headset me-1" viewBox="0 0 16 16">
                                <path
                                    d="M8 1a5 5 0 0 0-5 5v1h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a6 6 0 1 1 12 0v6a2.5 2.5 0 0 1-2.5 2.5H9.366a1 1 0 0 1-.866.5h-1a1 1 0 1 1 0-2h1a1 1 0 0 1 .866.5H11.5A1.5 1.5 0 0 0 13 12h-1a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h1V6a5 5 0 0 0-5-5z" />
                            </svg>
                            Butuh bantuan?
                        </span>
                    </div>
                    <p class="small text-muted">
                        Lupa Active User atau password? <br>
                        <a href="#" class="text-decoration-none fw-semibold">Hubungi layanan pelanggan kami</a>
                    </p>
                </div>
            </div>

            <!-- Footer dengan informasi tambahan -->
            <div class="modal-footer bg-light border-0 rounded-bottom-4 justify-content-center">
                <div class="text-center small text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-shield-check me-1" viewBox="0 0 16 16">
                        <path
                            d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z" />
                        <path
                            d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z" />
                    </svg>
                    Login aman dengan koneksi terenkripsi
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom styling */
    .modal-content {
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .btn-primary:focus {
        box-shadow: 0 0 0 0.25rem rgba(75, 140, 183, 0.25);
    }

    .input-group .form-control:focus {
        border-color: #4b6cb7;
    }

    .btn-primary:hover {
        opacity: 0.9;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(24, 40, 72, 0.3);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert {
        animation: fadeIn 0.5s ease-out;
    }

    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out;
    }

    .modal.show .modal-dialog {
        transform: none;
    }
</style>

<script>
    // Aktifkan tooltip
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('modal_password_input');
        const icon = this.querySelector('i');

        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        } else {
            password.type = 'password';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    });
</script>
