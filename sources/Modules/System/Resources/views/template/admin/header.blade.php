<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <link rel="icon" href="{{ asset('public/assetsku/img/logotsu.png') }}" type="image/png"/>
    <title>TSU - {{$title}}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{asset('public/assets/plugins/fontawesome-free-7.1.0-web/css/all.min.css')}}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{asset('public/assets/plugins/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="{{asset('public/assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css')}}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{asset('public/assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
    <!-- JQVMap -->
    <link rel="stylesheet" href="{{asset('public/assets/plugins/jqvmap/jqvmap.min.css')}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset('public/assets/dist/css/adminlte.min.css')}}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{asset('public/assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{asset('public/assets/plugins/daterangepicker/daterangepicker.css')}}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{asset('public/assets/plugins/summernote/summernote-bs4.min.css')}}">
    {{-- loading --}}
    <link rel="stylesheet" href="{{ asset('public/assets/dist/css/loading.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">
    <!-- SweetAlert 2 -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    {{-- Jquery UI --}}
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/jquery-ui/jquery-ui.css') }}">

    {{-- TSU Global Design System --}}
    <link rel="stylesheet" href="{{ asset('public/assetsku/css/tsu-theme.css') }}?v={{ @filemtime(base_path('../public/assetsku/css/tsu-theme.css')) ?: '2.0' }}">
    @yield('link_href')
    @yield('css')
    <style>
        .content-wrapper {
            min-height: calc(100vh - 114px) !important;
        }

        /* ============================================
           TSU Brand Color Theme - Navbar & Footer
           Primary: #094b54 (teal dark)
           Secondary: #1d7a87 (teal mid)
           Accent: #f8c12a (gold)
        ============================================ */

        /* === TOP NAVBAR === */
        .main-header.navbar {
            background: linear-gradient(135deg, #094b54 0%, #1d7a87 60%, #094b54 100%) !important;
            border-bottom: 2px solid #f8c12a !important;
            box-shadow: 0 2px 8px rgba(9, 75, 84, 0.4);
        }

        /* Navbar links & icons */
        .main-header.navbar .nav-link,
        .main-header.navbar .nav-link i,
        .main-header.navbar .navbar-nav > li > a {
            color: #e0f4f6 !important;
            transition: color 0.2s ease;
        }
        .main-header.navbar .nav-link:hover,
        .main-header.navbar .nav-link:focus {
            color: #f8c12a !important;
        }

        /* "Online" status text */
        .main-header.navbar .nav-link .text-success {
            color: #7fe0b0 !important;
        }

        /* Notification badge */
        .main-header.navbar .navbar-badge {
            background-color: #f8c12a !important;
            color: #063940 !important;
            font-weight: 700;
        }

        /* Username text di navbar kanan */
        .main-header.navbar .d-none.d-md-inline {
            color: #ffffff !important;
        }

        /* Hamburger / bars icon */
        .main-header.navbar [data-widget="pushmenu"] {
            color: #f8c12a !important;
        }
        .main-header.navbar [data-widget="pushmenu"]:hover {
            color: #fff !important;
        }

        /* Dropdown notif & user menu - tetap putih biar readable */
        .main-header.navbar .dropdown-menu {
            border-top: 3px solid #1d7a87;
        }
        .main-header.navbar .dropdown-menu .dropdown-header {
            background-color: #f0fafc !important;
            color: #063940 !important;
        }

        /* === FOOTER === */
        .main-footer {
            background: linear-gradient(135deg, #094b54 0%, #1d7a87 100%) !important;
            border-top: 2px solid #f8c12a !important;
            color: #c8ecf0 !important;
            box-shadow: 0 -2px 8px rgba(9, 75, 84, 0.3);
        }
        .main-footer a {
            color: #f8c12a !important;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .main-footer a:hover {
            color: #ffffff !important;
            text-decoration: underline;
        }
        .main-footer strong {
            color: #e8f8fa;
        }
        .main-footer b,
        .main-footer .float-right {
            color: #a8dce3 !important;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
    @include('system::template/admin/sidebar')
    @include('system::template/admin/navbar')
    <div class="content-wrapper">
        @yield('content')
    </div>
    @include('system::template/admin/loading')
    @include('system::template/admin/footer')
</div>
</body>
</html>
