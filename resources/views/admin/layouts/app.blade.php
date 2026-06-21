<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin - {{ config('app.name', 'AniKoto') }}</title>

    {{-- Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    {{-- Favicon --}}
    @php
        $faviconPath = Cache::remember('setting_favicon', 1800, fn() =>
            \App\Models\Setting::where('key', 'favicon')->value('value'));

        $faviconUrl = $faviconPath && \Illuminate\Support\Str::startsWith($faviconPath, 'http')
            ? $faviconPath
            : ($faviconPath ? Storage::url($faviconPath) : asset('favicon.ico'));
    @endphp

    <link rel="icon" href="{{ $faviconUrl }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#0b0e16] text-gray-200 font-sans antialiased">

<div class="flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    <aside class="w-64 bg-gray-900 border-r border-gray-700 p-4 flex flex-col"
           x-data="{ panel: localStorage.getItem('admin_panel') || 'anime' }"
           x-init="$watch('panel', val => localStorage.setItem('admin_panel', val))">

        {{-- LOGO --}}
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center gap-2 mb-6 text-indigo-500 font-bold text-lg">
            🎬 Admin
        </a>

        {{-- PANEL SWITCH --}}
        <div class="flex bg-gray-800 rounded-lg p-1 text-xs mb-4">
            <button @click="panel='anime'"
                :class="panel==='anime' ? 'bg-indigo-600 text-white' : 'text-gray-400'"
                class="flex-1 py-1 rounded transition">
                Anime
            </button>
            <button @click="panel='manga'"
                :class="panel==='manga' ? 'bg-emerald-600 text-white' : 'text-gray-400'"
                class="flex-1 py-1 rounded transition">
                Manga
            </button>
        </div>

        {{-- ANIME NAV --}}
        <nav x-show="panel==='anime'" class="space-y-1 flex-1">

            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                Dashboard
            </a>

            <a href="{{ route('admin.anime.index') }}"
               class="nav-link {{ request()->routeIs('admin.anime.*') ? 'active' : '' }}">
                Anime
            </a>

            <a href="{{ route('admin.featured.index') }}"
               class="nav-link {{ request()->routeIs('admin.featured.*') ? 'active' : '' }}">
                Featured
            </a>

            <a href="{{ route('admin.genres.index') }}"
               class="nav-link {{ request()->routeIs('admin.genres.*') ? 'active' : '' }}">
                Genres
            </a>

            <a href="{{ route('admin.jikan.search') }}"
               class="nav-link">
                MAL Import
            </a>

            <hr class="border-gray-700 my-3">

            <a href="{{ route('admin.users.index') }}" class="nav-link">Users</a>
            <a href="{{ route('admin.comments.index') }}" class="nav-link">Comments</a>
            <a href="{{ route('admin.reports.index') }}" class="nav-link">Reports</a>
            <a href="{{ route('admin.settings.index') }}" class="nav-link">Settings</a>

        </nav>

        {{-- MANGA NAV --}}
        <nav x-show="panel==='manga'" x-cloak class="space-y-1 flex-1">

            <a href="{{ route('admin.manga.dashboard') }}" class="nav-link">Dashboard</a>
            <a href="{{ route('admin.manga.index') }}" class="nav-link">Manga</a>
            <a href="{{ route('admin.manga.genres.index') }}" class="nav-link">Genres</a>

            <hr class="border-gray-700 my-3">

            <a href="{{ route('admin.comments.index') }}" class="nav-link">Comments</a>
            <a href="{{ route('admin.users.index') }}" class="nav-link">Users</a>
            <a href="{{ route('admin.settings.index') }}" class="nav-link">Settings</a>

        </nav>

        {{-- BACK --}}
        <a href="{{ route('home') }}"
           class="mt-4 text-sm text-gray-500 hover:text-white transition">
            ← Back to site
        </a>

    </aside>

    {{-- MAIN --}}
    <main class="flex-1 overflow-y-auto p-6 space-y-6">
        @yield('content')
    </main>

</div>

{{-- ✅ TOAST SYSTEM --}}
<div x-data="toastCenter()" x-init="init()" x-cloak>
    <div
        x-show="showToast"
        x-transition.opacity.duration.300ms
        class="fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-xl text-sm"
        :class="type === 'success'
            ? 'bg-green-500/20 text-green-300 border border-green-500/30'
            : 'bg-red-500/20 text-red-300 border border-red-500/30'">
        <span x-text="message"></span>
    </div>
</div>

@if(session('success'))
<div x-data x-init="$dispatch('toast', { message: @js(session('success')), type: 'success' })"></div>
@endif

@if(session('error'))
<div x-data x-init="$dispatch('toast', { message: @js(session('error')), type: 'error' })"></div>
@endif

{{-- NAV STYLE --}}
<style>
.nav-link {
    @apply block px-3 py-2 rounded text-sm text-gray-400 hover:text-white hover:bg-gray-800 transition;
}
.nav-link.active {
    @apply bg-indigo-600 text-white;
}
</style>

@stack('scripts')

</body>
</html>