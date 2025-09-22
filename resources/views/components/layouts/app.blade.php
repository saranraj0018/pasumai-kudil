<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ __('Dashboard') }} - {{ config('app.name') }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
     @livewireStyles
    @fluxAppearance

</head>
<body class="bg-[#f5f7fa] text-black">

<div x-data="{ open: true }" class="flex min-h-screen">

    {{-- Sidebar --}}
    <x-sidebar />

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col">

        {{-- Navbar --}}
        <x-navbar :title="$title ?? __('Dashboard')" />

        {{-- Page Content --}}
        <main class="p-6 ">
            {{ $slot }}
        </main>
    </div>
</div>
@livewireScripts
@fluxScripts

</body>
</html>
