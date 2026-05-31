<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AniWaves') }} - @yield('title', 'Watch Anime')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @php
        $faviconPath = \Illuminate\Support\Facades\Cache::remember('setting_favicon', 1800, fn() => \App\Models\Setting::where('key', 'favicon')->value('value'));
        $faviconUrl = $faviconPath ? (\Illuminate\Support\Str::startsWith($faviconPath, 'http') ? $faviconPath : \Illuminate\Support\Facades\Storage::url($faviconPath)) : null;
    @endphp
    <link rel="icon" type="image/x-icon" href="{{ $faviconUrl ?: asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-950 text-white">
    <div class="min-h-screen flex flex-col">
        @include('layouts.partials.header')

        <main class="flex-1">
            @yield('content')
        </main>

        @include('layouts.partials.footer')
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
