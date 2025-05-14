{{-- Modal hanya akan di-render jika pelanggan belum login --}}
{{-- @guest('customer_web') --}} {{-- Kondisi ini bisa dihilangkan jika modal selalu di-include di layout utama dan tombol pemicunya yang dikondisikan --}}
<div class="modal fade" id="customerLoginModal" tabindex="-1" aria-labelledby="customerLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title" id="customerLoginModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
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
            <div class="modal-body p-4 p-lg-5">
                <form method="POST" action="{{ route('customer.login.attempt') }}" id="customerLoginFormInsideModal">
                    @csrf

                    @if ($errors->customer_login->any())
                        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->customer_login->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="modal_active_user_input" class="form-label">ID Pengguna (Active User)</label>
                        {{-- ID input diubah --}}
                        <input id="modal_active_user_input" type="text"
                            class="form-control rounded-3 @error('active_user_modal', 'customer_login') is-invalid @enderror"
                            name="active_user_modal" value="{{ old('active_user_modal') }}" required
                            autocomplete="username" autofocus placeholder="Masukkan ID Pengguna Anda">
                    </div>

                    <div class="mb-3">
                        <label for="modal_password_input" class="form-label">Password</label> {{-- ID input diubah --}}
                        <input id="modal_password_input" type="password"
                            class="form-control rounded-3 @error('password_modal', 'customer_login') is-invalid @enderror"
                            name="password_modal" required autocomplete="current-password"
                            placeholder="Masukkan password">
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember_modal"
                                id="modal_remember_customer_input" {{ old('remember_modal') ? 'checked' : '' }}>
                            {{-- ID input diubah --}}
                            <label class="form-check-label" for="modal_remember_customer_input">
                                Ingat Saya
                            </label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-semibold py-2">
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
                <div class="text-center mt-4">
                    <small class="text-muted">Lupa ID Pengguna atau password? <br>Silakan hubungi layanan
                        pelanggan kami.</small>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- @endguest --}}
