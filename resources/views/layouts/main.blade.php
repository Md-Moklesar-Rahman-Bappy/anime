<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'AniKoto'))</title>

    {{-- ✅ FAVICON (cached properly) --}}
    <link rel="icon" href="{{ config('app.favicon', asset('favicon.ico')) }}">

    {{-- ✅ FONT --}}
    https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap

    {{-- ✅ ICONS --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    {{-- ✅ VIDEO PLAYER CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css" rel="stylesheet" />

    {{-- ✅ TAILWIND (VITE) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="bg-[#0a0a0f] text-gray-200 font-sans antialiased">

<div class="min-h-screen flex flex-col">

    {{-- ✅ HEADER --}}
    @include('layouts.partials.header')

    {{-- ✅ MAIN --}}
    <main class="flex-grow">

        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </div>

    </main>

    {{-- ✅ FOOTER --}}
    @include('layouts.partials.footer')

</div>

{{-- ✅ MODALS --}}
@stack('modals')

{{-- ✅ SCRIPTS --}}
@stack('scripts')

{{-- ✅ VIDEO PLAYER --}}
<script src="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.min.js"></script>

</body>
</html>