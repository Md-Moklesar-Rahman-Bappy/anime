<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ─────── DYNAMIC TITLE ─────── --}}
    <title>{{ $title ?? ($pageTitle ?? null) ? ($title ?? $pageTitle) . ' · ' . config('app.name', 'AniKoto') : config('app.name', 'AniKoto') . ' — Watch Anime Online Free' }}</title>

    {{-- ─────── SEO META ─────── --}}
    @php
        $metaTitle       = $title ?? ($pageTitle ?? config('app.name', 'AniKoto'));
        $metaDescription = $description ?? 'Watch and read your favorite anime and manga free on AniKoto. HD streaming, daily updates, no ads.';
        $metaImage       = $ogImage ?? asset('og-default.jpg');
        $metaUrl         = url()->current();
    @endphp

    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="anime, manga, watch anime, anime online, free anime, AniKoto">
    <meta name="robots" content="index, follow">

    {{-- Open Graph (Facebook, Discord) --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:url" content="{{ $metaUrl }}">
    <meta property="og:site_name" content="{{ config('app.name', 'AniKoto') }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">

    {{-- Canonical --}}
    <link rel="canonical" href="{{ $metaUrl }}">

    {{-- ─────── FAVICON ─────── --}}
    @php
        $faviconPath = Cache::remember('setting_favicon', 1800, fn() =>
            \App\Models\Setting::where('key', 'favicon')->value('value'));

        $faviconUrl = $faviconPath && \Illuminate\Support\Str::startsWith($faviconPath, 'http')
            ? $faviconPath
            : ($faviconPath ? Storage::url($faviconPath) : asset('favicon.ico'));
    @endphp
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

    {{-- ─────── FONTS ─────── --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- ─────── ICONS ─────── --}}
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- ─────── VITE ─────── --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- ─────── PAGE HEAD HOOK ─────── --}}
    @stack('head')
</head>

<body class="bg-[#0b0e16] text-gray-200 font-sans antialiased flex flex-col min-h-screen">

    {{-- ─────── HEADER ─────── --}}
    @include('layouts.partials.header')

    {{-- ─────── MAIN ─────── --}}
    <main class="flex-grow">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </main>

    {{-- ─────── FOOTER ─────── --}}
    @include('layouts.partials.footer')

    {{-- ─────── SCROLL TO TOP ─────── --}}
    <button
        x-data="{ show: false }"
        @scroll.window="show = window.scrollY > 400"
        x-show="show"
        x-cloak
        x-transition
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        aria-label="Scroll to top"
        class="fixed bottom-6 right-6 z-40 w-11 h-11 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white shadow-xl flex items-center justify-center transition focus:outline-none focus:ring-2 focus:ring-indigo-400"
    >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
        </svg>
    </button>

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

                    <div class="mt-2 flex gap-3" x-show="t.actionLabel || t.dismissLabel">
                        <button type="button" x-show="t.actionLabel"
                                @click="runAction(t.id)"
                                class="text-xs font-semibold underline hover:no-underline"
                                x-text="t.actionLabel"></button>

                        <button type="button" x-show="t.dismissLabel"
                                @click="dismiss(t.id)"
                                class="text-xs opacity-70 hover:opacity-100"
                                x-text="t.dismissLabel"></button>
                    </div>
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

    {{-- ─────── MODALS ─────── --}}
    @stack('modals')

    {{-- ─────── SCRIPTS ─────── --}}
    @stack('scripts')

    {{-- ─────── ANALYTICS (PROD ONLY) ─────── --}}
    @production
        @stack('analytics')
    @endproduction

</body>
</html>