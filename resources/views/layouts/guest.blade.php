<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AniKoto') }}</title>

    {{-- ✅ GOOGLE FONT --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- ✅ ICONS --}}
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    {{-- ✅ VITE (Tailwind CSS) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex items-center justify-center bg-[#0a0a0f] text-gray-200 font-sans antialiased">

    {{-- ✅ AUTH WRAPPER --}}
    <div class="w-full max-w-md px-4">

        {{-- ✅ LOGO --}}
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="text-2xl font-bold tracking-wide">
                🎬 AniKoto
            </a>
        </div>

        {{-- ✅ CARD --}}
        <div class="bg-[#111827] border border-gray-700 rounded-2xl shadow-xl p-6">
            {{ $slot }}
        </div>

        {{-- ✅ FOOTER --}}
        <p class="text-center text-xs text-gray-500 mt-6">
            © {{ date('Y') }} AniKoto. All rights reserved.
        </p>

    </div>

</body>
</html>