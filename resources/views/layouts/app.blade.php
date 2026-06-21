<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AniKoto') }}</title>

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    {{-- ✅ Vite (Tailwind) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#0b0e16] text-gray-200 font-sans antialiased">

    <div class="min-h-screen flex flex-col">

        {{-- ✅ NAVBAR --}}
        @include('layouts.partials.header')

        {{-- ✅ MAIN CONTENT --}}
        <main class="flex-grow">

            <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
                {{ $slot }}
            </div>

        </main>

        {{-- ✅ FOOTER --}}
        @include('layouts.partials.footer')

    </div>

    {{-- ✅ MODALS --}}
    @stack('modals')

    {{-- ✅ SCRIPTS --}}
    @stack('scripts')

</body>
</html>
