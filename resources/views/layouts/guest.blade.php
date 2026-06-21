<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ─────── TITLE ─────── --}}
    <title>@yield('title', 'Welcome') · {{ config('app.name', 'AniKoto') }}</title>

    {{-- ─────── SEO META ─────── --}}
    <meta name="description"
          content="Sign in to AniKoto — watch anime and read manga free in HD. Daily updates, no ads.">
    <meta name="robots" content="noindex, nofollow">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ config('app.name', 'AniKoto') }}">
    <meta property="og:description" content="Watch anime & read manga free.">
    <meta property="og:url" content="{{ url()->current() }}">

    {{-- ─────── FAVICON ─────── --}}
    @php
        $faviconPath = Cache::remember('setting_favicon', 1800, fn() =>
            \App\Models\Setting::where('key', 'favicon')->value('value'));

        $faviconUrl = $faviconPath && \Illuminate\Support\Str::startsWith($faviconPath, 'http')
            ? $faviconPath
            : ($faviconPath ? Storage::url($faviconPath) : asset('favicon.ico'));
    @endphp
    {{ $faviconUrl }}

    {{-- ─────── FONTS ─────── --}}
    https://fonts.googleapis.com
    .gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- ─────── ICONS ─────── --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- ─────── VITE ─────── --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>

<body class="min-h-screen bg-[#0b0e16] text-gray-200 font-sans antialiased relative overflow-x-hidden">

    {{-- ─────── DECORATIVE BACKGROUND GLOW ─────── --}}
    <div class="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-indigo-500/10 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-purple-500/10 blur-3xl"></div>
    </div>

    {{-- ─────── PAGE CONTENT ─────── --}}
    {{ $slot ?? '' }}
    @yield('content')

    {{-- ─────── TOAST CENTER ─────── --}}
    <div
        x-data="toastCenter()"
        x-init="init()"
        x-cloak
        class="fixed bottom-5 right-5 z-[9999] flex flex-col gap-2 max-w-sm w-full"
    >
        <template x-for="t in toasts" :key="t.id">
            <div
                x-show="t.show"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="rounded-lg border px-4 py-3 shadow-lg backdrop-blur-sm flex items-start gap-2"
                :class="colorFor(t.type)"
            >
                <span class="font-bold" x-text="iconFor(t.type)"></span>

                <div class="flex-1 text-sm">
                    <p x-text="t.message"></p>
                </div>

                <button type="button" @click="dismiss(t.id)"
                        class="opacity-60 hover:opacity-100 text-sm"
                        aria-label="Close">
                    ✕
                </button>
            </div>
        </template>
    </div>

    {{-- ─────── FLASH MESSAGES → TOAST ─────── --}}
    @if(session('success'))
        <div x-data x-init="$dispatch('toast', { message: @js(session('success')), type: 'success' })"></div>
    @endif

    @if(session('error'))
        <div x-data x-init="$dispatch('toast', { message: @js(session('error')), type: 'error' })"></div>
    @endif

    @if(session('info'))
        <div x-data x-init="$dispatch('toast', { message: @js(session('info')), type: 'info' })"></div>
    @endif

    @if(session('warning'))
        <div x-data x-init="$dispatch('toast', { message: @js(session('warning')), type: 'warning' })"></div>
    @endif

    @stack('scripts')
</body>
</html>