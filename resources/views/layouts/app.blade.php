<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard :: PasumaiKudil</title>

    <!-- Google Font -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/fontawesome-free/css/all.min.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/2.3.0/alpine.js"></script>

    @vite(['resources/css/app.css'])

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="hold-transition sidebar-mini bg-gray-100">
<div class="wrapper">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="bg-gradient-to-b from-[#ab5f00] to-white fixed top-0 left-0 h-full w-64 shadow-lg">
            @include('layouts.sidebar')
        </aside>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 ml-64">
            <!-- Navbar -->
            <nav class="bg-white w-full border-b border-gray-200 px-5 py-3 shadow-sm">
                <a href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </nav>

            <!-- Page Content -->
            <div class="content-wrapper p-6">
                @yield('content')
            </div>
        </div>
    </div>

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="{{ asset('admin/plugins/jquery/jquery.min.js') }}"></script>

<!-- Bootstrap 4 -->
<script src="{{ asset('admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<!-- AdminLTE App -->
<script src="{{ asset('admin/js/adminlte.min.js') }}"></script>

<!-- Custom JS -->
<script src="{{ asset('admin/js/custom.js') }}"></script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>

@yield('customJs')
@yield('customCss')
</body>
</html>
