<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Authentication' }} - Pasumaikudil</title>

    <!-- Tailwind -->
    @vite(['resources/css/app.css'])

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>

    <!-- Alpine.js -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('customCss')
</head>
<body class="min-h-screen bg-gradient-to-r from-[#FFE6CE] to-white flex items-center justify-center">
{{-- Content (login/register will inject here) --}}
@yield('content')

<footer class="absolute bottom-4 w-full text-center text-gray-500 text-sm">
    &copy; {{ date('Y') }} Pasumaikudil. All rights reserved.
</footer>

@yield('customJs')
</body>
</html>
