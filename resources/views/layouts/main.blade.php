<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Title -->
    <title>@yield('title', config('app.name', 'AniWaves'))</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Plyr CSS -->
    <link href="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css" rel="stylesheet">

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

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body style="background:#0a0a0f;color:#fff;font-family:Inter,sans-serif;">

<div class="d-flex flex-column min-vh-100">

    <!-- ✅ HEADER -->
    @include('layouts.partials.header')

    <!-- ✅ MAIN CONTENT -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- ✅ FOOTER -->
    @include('layouts.partials.footer')

</div>

<!-- ✅ MODALS -->
@stack('modals')

<!-- ✅ SCRIPTS -->
@stack('scripts')

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>