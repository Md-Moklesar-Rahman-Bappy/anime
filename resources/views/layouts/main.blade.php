<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Title -->
    <title>
        {{ config('app.name', 'AniWaves') }}
        @hasSection('title') - @yield('title') @endif
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Favicon -->
    @php
        $faviconPath = cache()->remember('setting_favicon', 1800,
            fn() => \App\Models\Setting::where('key', 'favicon')->value('value'));

        $faviconUrl = $faviconPath
            ? (str_starts_with($faviconPath, 'http')
                ? $faviconPath
                : \Illuminate\Support\Facades\Storage::url($faviconPath))
            : asset('favicon.ico');
    @endphp

    <link rel="icon" href="{{ $faviconUrl }}">

    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="bg-[#0a0a0f] text-white font-sans antialiased">

<div class="min-h-screen flex flex-col">

    <!-- ✅ HEADER -->
    @include('layouts.partials.header')

    <!-- ✅ MAIN CONTENT -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- ✅ FOOTER -->
    @include('layouts.partials.footer')

</div>

<!-- ✅ MODALS -->
@stack('modals')

<!-- ✅ SCRIPTS -->
@stack('scripts')

</body>
</html>