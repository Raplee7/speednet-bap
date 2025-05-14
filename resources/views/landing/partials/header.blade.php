<header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

        <a href="{{ route('landing.page') }}" class="d-flex align-items-center me-auto">
            <img src="{{ asset('cust-assets/img/speednet-logo.png') }}" alt="Logo Speednet BAP" style="max-height: 50px;">
            {{-- <h1 class="sitename ms-2">{{ config('app.name', 'Speednet BAP') }}</h1> --}}
        </a>

        <nav id="navmenu" class="navmenu">
            <ul>
                {{-- Fungsi helper untuk menentukan kelas 'active' --}}
                @php
                    if (!function_exists('isActiveRoute')) {
                        function isActiveRoute(string $fragmentName): string
                        {
                            // PERBAIKAN: Menggunakan helper request()
                            return request()->fragment() === $fragmentName ||
                                (request()->is('/') && empty(request()->fragment()) && $fragmentName === 'hero')
                                ? 'active'
                                : '';
                        }
                    }
                @endphp
                <li><a href="{{ route('landing.page') }}#hero" class="{{ isActiveRoute('hero') }}">Beranda</a></li>
                <li><a href="{{ route('landing.page') }}#tentang" class="{{ isActiveRoute('tentang') }}">Tentang</a>
                </li>
                <li><a href="{{ route('landing.page') }}#paket" class="{{ isActiveRoute('paket') }}">Paket</a></li>
                <li><a href="{{ route('landing.page') }}#faq" class="{{ isActiveRoute('faq') }}">FAQ</a></li>
                <li><a href="{{ route('landing.page') }}#form" class="{{ isActiveRoute('form') }}">Form
                        Pendaftaran</a></li>

                @auth('customer_web')
                    {{-- Menggunakan directive @auth dengan guard --}}
                    <li class="dropdown">
                        <a href="#" class="d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                                class="bi bi-person-fill-gear me-2" viewBox="0 0 16 16">
                                <path
                                    d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 8c0 1 1 1 1 1h5.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.544-3.393C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4Zm9.886-3.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382l.045-.148ZM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z" />
                            </svg>
                            <span>Halo, {{ Str::words(Auth::guard('customer_web')->user()->nama_customer, 1, '') }}</span>
                            <i class="bi bi-chevron-down toggle-dropdown ms-auto"></i>
                        </a>
                        <ul>
                            <li><a href="{{ route('customer.dashboard') }}">Dashboard Saya</a></li>
                            {{-- Tambahkan link lain di sini, misalnya: --}}
                            {{-- <li><a href="{{ route('customer.payments.index') }}">Tagihan Saya</a></li> --}}
                            {{-- <li><a href="{{ route('customer.profile.show') }}">Profil Saya</a></li> --}}
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a href="#"
                                    onclick="event.preventDefault(); document.getElementById('customer-logout-form-header').submit();">
                                    Logout
                                </a>
                                <form id="customer-logout-form-header" action="{{ route('customer.logout') }}"
                                    method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                @endauth

                <li><a href="{{ route('landing.page') }}#contact" class="{{ isActiveRoute('contact') }}">Kontak</a>
                </li>
            </ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

        @guest('customer_web')
            <a class="btn-getstarted flex-md-shrink-0" href="#" data-bs-toggle="modal"
                data-bs-target="#customerLoginModal">Login Pelanggan</a>
        @endguest

    </div>
</header>
