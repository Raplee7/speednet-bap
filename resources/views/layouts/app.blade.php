<!doctype html>
<html lang="en" dir="ltr" data-bs-theme="light" data-bs-theme-color="theme-color-default">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Speednet')</title>


    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/logobpp.ico') }}">

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('assets/css/core/libs.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/aos/dist/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/swiperSlider/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css?v=5.0.0') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.min.css?v=5.0.0') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.min.css?v=5.0.0') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/rtl.min.css?v=5.0.0') }}">

    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">


    @stack('styles')
</head>

<body>
    <!-- Sidebar -->
    @include('partials.sidebar')

    <!-- Main Content -->
    <main class="main-content">
        <!-- Navbar -->
        @include('partials.navbar')

        <!-- Page Content -->
        <div class="container-fluid content-inner mt-n5 py-0">
            @yield('content')
        </div>

        <!-- Footer -->
        @include('partials.footer')
    </main>

    <!-- Setting Button and Offcanvas -->
    @include('partials.setting')

    <!-- Scripts -->
    <script src="{{ asset('assets/vendor/swiperSlider/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/libs.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/external.min.js') }}"></script>
    <script src="{{ asset('assets/js/charts/widgetcharts.js') }}"></script>
    <script src="{{ asset('assets/js/charts/vectore-chart.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="{{ asset('assets/js/charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/js/charts/dashboard.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/fslightbox.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/setting.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/form-wizard.js') }}"></script>
    <script src="{{ asset('assets/vendor/aos/dist/aos.js') }}"></script>
    <script>
        AOS.init();
    </script>
    <script src="{{ asset('assets/js/hope-ui.js') }}" defer></script>
    <script src="{{ asset('assets/js/plugins/calender.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/prism.mini.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/circle-progress.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
    </script>



    @stack('scripts')
</body>

</html>
