<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Title -->
    <title>{{ config('app.name', 'AniWaves') }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body style="background:#0b0e16;color:#fff;font-family:figtree,sans-serif;">

    <div class="d-flex flex-column min-vh-100">

        <!-- ✅ HEADER -->
        @include('layouts.partials.header')

        <!-- ✅ CONTENT -->
        <main class="flex-grow-1">
            {{ $slot }}
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