<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="base_url" content="{{ url('/') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' : '' }}Trade Exchange</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    @stack('css-plugins')

    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.min.css') }}">
    @stack('css')
    <style>
        .dropify-message p {
            font-size: 20px
        }

        hr {
            border: 0;
            clear: both;
            display: block;
            width: 96%;
            background-color: black;
            height: 2px;
        }

        .no-label {
            margin-top: 1.9rem !important;
        }

        .scrolledTable {
            overflow-y: auto;
            clear: both;
        }

        .notification-container {
            position: absolute;
            padding: 5px 8px;
            top: -5px;
            right: 30px;
            width: auto;
            display: none;
            height: 42px;
            overflow: hidden;
            background: gray;
            z-index: 999;
            transform: translateX(100%);
            -webkit-transform: translateX(100%);
        }

        .open-button-container {
            animation: float-in 0.5s forwards;
            -webkit-animation: float-in 0.5s forwards;
        }

        .close-button-container {
            animation: float-out 0.5s forwards;
            -webkit-animation: float-out 0.5s forwards;
        }

        .nolabel {
            background-color: transparent;
            color: transparent;
        }

        @keyframes float-in {
            0% {
                -webkit-transform: translateX(100%);
            }

            100% {
                -webkit-transform: translateX(0%);
            }
        }

        @-webkit-keyframes float-in {
            0% {
                transform: translateX(100%);
            }

            100% {
                transform: translateX(0%);
            }
        }

        @keyframes float-out {
            0% {
                transform: translateX(0%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        @-webkit-keyframes float-out {
            0% {
                -webkit-transform: translateX(0%);
            }

            100% {
                -webkit-transform: translateX(100%);
            }
        }

        .greenClass,
        .greenClass td {
            background-color: #00a65a !important;
        }

        .yellowClass,
        .yellowClass td {
            background-color: #f39c12 !important;
        }

        .redClass,
        .redClass td {
            background-color: #df4526e8 !important;
        }

        td.dt-control.indicator-white::before {
            background-color: white !important;
            color: #31b131 !important;
        }

        .ui-draggable-handle {
            cursor: move;
        }

        .wrapper_scroll_top {
            overflow-x: auto;
            overflow-y: hidden;
            height: 20px;
        }

        .wrapper_scroll_top>div {
            height: 20px;
        }

        .modal-xxl {
            max-width: 85%;
        }

        .modal-fullscreen {
            max-width: 94%;
        }

        .table td,
        .table th {
            padding: 0.5rem !important;
        }
    </style>
</head>

<body class="{{ $classBody ?? 'hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed' }}">
    @auth
        <div class="wrapper">

            <!-- Preloader -->
            <div class="preloader flex-column justify-content-center align-items-center">
                <img class="animation__wobble" src="{{ asset('assets/img/AdminLTELogo.png') }}" alt="AdminLTELogo"
                    height="60" width="60">
            </div>
            @include('components.navbar')
            <x-sidebar></x-sidebar>
            <div class="content-wrapper">
                {{-- @include('components.breadcumbs', ['titleHeader' => $titleHeader ?? '']) --}}
                <section class="content" id="contentBody">
                    @yield('body')
                </section>
            </div>

            @include('components.footer')

            @include('components.theme-control')

            <div id="sidebar-overlay"></div>
        </div>
    @endauth

    @guest
        @yield('body')
    @endguest

    @auth
        <x-base-modal formAction="{{ route('ganti-password') }}" formId="formGantiPassword" modalId="modalGantiPassword"
            modalWidth="md" modalTitle="Ganti Password">
            @method('PUT')
            <div class="row">
                <x-form-part col="sm-12" type="password" label="Password Lama" name="oldUID"
                    placeholder="Masukkan Password Lama" required />
                <x-form-part col="sm-12" type="password" label="Password Baru" name="UID2"
                    placeholder="Masukkan Password Baru" required />
                <x-form-part col="sm-12" type="password" label="Password Lama" name="UID2_confirmation"
                    placeholder="Ulangi Password Baru" required />
            </div>
        </x-base-modal>
        <x-base-modal formAction="{{ route('berkas.set-periode') }}" formId="formSetPeriode" modalId="modalSetPeriode"
            modalWidth="md" modalTitle="Setup Periode">
            @method('PUT')
            <div class="row">
                <x-form-part col="sm-6" type="number" min="01" max="12"
                    onchange="this.value=('0'.repeat(2)+this.value.toString()).slice(-2)" label="Bulan" name="BULAN"
                    required />
                <x-form-part col="sm-6" type="number" min="2000" label="Tahun" name="TAHUN" required />
            </div>
        </x-base-modal>
    @endauth
    @stack('modal')
    @stack('offcanvas')

    <section id="$scriptFile">
        <!-- REQUIRED SCRIPTS -->
        <!-- jQuery -->
        <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/jquery-form/jquery-form.min.js') }}"></script>
        <!-- Bootstrap -->
        <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
        @stack('js-plugins')
        <!-- overlayScrollbars -->
        <script src="{{ asset('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
        <!-- AdminLTE App -->
        <script src="{{ asset('assets/js/adminlte.js') }}"></script>

        <!-- PAGE PLUGINS -->
        <!-- jQuery Mapael -->
        <script src="{{ asset('assets/plugins/jquery-mousewheel/jquery.mousewheel.js') }}"></script>
        <script src="{{ asset('assets/plugins/raphael/raphael.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/jquery-mapael/jquery.mapael.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/jquery-mapael/maps/usa_states.min.js') }}"></script>
        <!-- ChartJS -->
        <script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/moment/moment.min.js') }}"></script>

        <div id="toastsContainerTopRight" class="toasts-top-right fixed">
        </div>
        @stack('js')
        @auth
            {{-- <script src="{{ asset('assets/js/base-function.js') }}"></script> --}}
            <script src="{{ asset('assets/plugins/jquery-maskmoney/jquery.maskMoney.js') }}"></script>
            <script src="{{ asset('assets/js/demo.js') }}"></script>
            <script src="{{ asset('assets/js/helper.js') }}" type="module"></script>
            <script>
                $(document).ready(function() {
                    // set default locale momenjs
                    moment.locale('id');
                })
            </script>
        @endauth
    </section>

</body>

</html>
