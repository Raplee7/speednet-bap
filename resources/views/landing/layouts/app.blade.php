<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    {{-- Judul halaman dinamis, dengan fallback ke nama aplikasi --}}
    <title>@yield('title', config('app.name', 'Speednet BAP'))</title>
    <meta name="description" content="{{ $metaDescription ?? 'Layanan internet cepat dan handal untuk Anda.' }}">
    <meta name="keywords" content="{{ $metaKeywords ?? 'internet, wifi, speednet, pontianak' }}">

    <link rel="icon" href="{{ asset('assets/images/wifiiconspeednet.ico') }}">
    <link href="{{ asset('cust-assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <link href="{{ asset('cust-assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('cust-assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('cust-assets/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('cust-assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('cust-assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

    <link href="{{ asset('cust-assets/css/main.css') }}" rel="stylesheet">

    {{-- Stack untuk CSS tambahan dari halaman spesifik --}}
    @stack('styles')
</head>

<body class="index-page"> {{-- Atau kelas body default template Anda --}}

    {{-- Include Partial Header --}}
    @include('landing.partials.header')

    <main class="main">
        {{-- Konten utama halaman akan di-render di sini --}}
        @yield('content')
    </main>

    {{-- Include Partial Footer --}}
    @include('landing.partials.footer')

    {{-- Include Partial Modal Login Pelanggan --}}
    {{-- Modal hanya akan di-render jika pelanggan belum login --}}
    @guest('customer_web')
        @include('landing.partials.customer_login_modal')
    @endguest

    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <script src="{{ asset('cust-assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('cust-assets/vendor/php-email-form/validate.js') }}"></script>
    <script src="{{ asset('cust-assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('cust-assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('cust-assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>
    <script src="{{ asset('cust-assets/vendor/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('cust-assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('cust-assets/vendor/swiper/swiper-bundle.min.js') }}"></script>

    <script src="{{ asset('cust-assets/js/main.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Script untuk membuka modal jika ada error login pelanggan dari redirect --}}
    @if (session('open_customer_login_modal') && $errors->customer_login->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var customerLoginModalElement = document.getElementById('customerLoginModal');
                if (customerLoginModalElement) {
                    var myModal = new bootstrap.Modal(customerLoginModalElement);
                    myModal.show();
                }
            });
        </script>
    @endif

    {{-- Script SweetAlert untuk notifikasi sukses dari session --}}
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: @json(session('success')),
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Oke'
                });
            });
        </script>
    @endif

    {{-- Script SweetAlert untuk notifikasi error umum dari session (bukan error validasi form) --}}
    {{-- Hanya tampilkan jika bukan error dari login customer untuk menghindari duplikasi alert --}}
    @if (session('error') && !$errors->customer_login->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: @json(session('error')),
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Tutup'
                });
            });
        </script>
    @endif

    {{-- Script SweetAlert untuk error validasi umum (jika ada dan bukan dari login customer) --}}
    @if (!$errors->customer_login->any() && $errors->any() && !session('success') && !session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let errorMessages = '';
                @foreach ($errors->all() as $error)
                    errorMessages += `{{ $error }}<br>`;
                @endforeach
                Swal.fire({
                    icon: 'error',
                    title: 'Oops! Ada Kesalahan Validasi',
                    html: errorMessages,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Tutup'
                });
            });
        </script>
    @endif

    {{-- Stack untuk script tambahan dari halaman spesifik --}}
    @stack('scripts')

</body>

</html>
