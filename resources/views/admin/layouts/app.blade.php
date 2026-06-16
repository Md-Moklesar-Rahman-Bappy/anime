<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin - {{ config('app.name', 'AniWaves') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    @php
        $faviconPath = Cache::remember('setting_favicon', 1800, fn() => \App\Models\Setting::where('key', 'favicon')->value('value'));
        $faviconUrl = $faviconPath && Str::startsWith($faviconPath, 'http')
            ? $faviconPath
            : ($faviconPath ? Storage::url($faviconPath) : asset('favicon.ico'));
    @endphp

    <link rel="icon" href="{{ $faviconUrl }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#0b0e16] text-white font-sans antialiased">

<div class="flex h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-[#111827] border-r border-gray-800 p-4 flex flex-col"
           x-data="{ panel: localStorage.getItem('admin_panel') || 'anime' }"
           x-init="$watch('panel', val => localStorage.setItem('admin_panel', val))">

        <!-- Logo -->
        <a href="{{ route('admin.dashboard') }}" class="flex items-center mb-6">
            <x-application-logo class="h-8 w-8 text-indigo-500"/>
            <span class="ml-2 font-bold text-white">Admin</span>
        </a>

        <!-- Toggle -->
        <div class="flex bg-[#1f2937] rounded-lg p-1 text-xs mb-4">
            <button @click="panel='anime'"
                :class="panel==='anime' ? 'bg-indigo-600 text-white' : 'text-gray-400'"
                class="flex-1 py-1.5 rounded">
                Anime
            </button>

            <button @click="panel='manga'"
                :class="panel==='manga' ? 'bg-emerald-600 text-white' : 'text-gray-400'"
                class="flex-1 py-1.5 rounded">
                Manga
            </button>
        </div>

        <!-- Anime Menu -->
        <nav x-show="panel==='anime'" class="space-y-2 flex-1">

            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.anime.index') }}" class="nav-link {{ request()->routeIs('admin.anime.*') ? 'active' : '' }}">Anime</a>
            <a href="{{ route('admin.featured.index') }}" class="nav-link {{ request()->routeIs('admin.featured.*') ? 'active' : '' }}">Featured</a>
            <a href="{{ route('admin.genres.index') }}" class="nav-link {{ request()->routeIs('admin.genres.*') ? 'active' : '' }}">Genres</a>
            <a href="{{ route('admin.jikan.search') }}" class="nav-link {{ request()->routeIs('admin.jikan.*') ? 'active' : '' }}">MAL Import</a>

            <div class="border-t border-gray-800 pt-2 mt-2">
                <a href="{{ route('admin.users.index') }}" class="nav-link">Users</a>
                <a href="{{ route('admin.comments.index') }}" class="nav-link">Comments</a>
                <a href="{{ route('admin.reports.index') }}" class="nav-link">Reports</a>
                <a href="{{ route('admin.settings.index') }}" class="nav-link">Settings</a>
            </div>

        </nav>

        <!-- Manga Menu -->
        <nav x-show="panel==='manga'" x-cloak class="space-y-2 flex-1">

            <a href="{{ route('admin.manga.dashboard') }}" class="nav-link">Dashboard</a>
            <a href="{{ route('admin.manga.index') }}" class="nav-link">Manga</a>
            <a href="{{ route('admin.manga.genres.index') }}" class="nav-link">Genres</a>

            <div class="border-t border-gray-800 pt-2 mt-2">
                <a href="{{ route('admin.comments.index') }}" class="nav-link">Comments</a>
                <a href="{{ route('admin.users.index') }}" class="nav-link">Users</a>
                <a href="{{ route('admin.settings.index') }}" class="nav-link">Settings</a>
            </div>

        </nav>

        <a href="{{ route('home') }}" class="mt-4 text-sm text-gray-500 hover:text-white">
            ← Back to site
        </a>

    </aside>

    <!-- Content -->
    <main class="flex-1 overflow-y-auto p-6">

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')

    </main>

</div>

<!-- Styles -->
<style>
.nav-link {
    @apply block px-4 py-2 rounded-lg text-sm text-gray-400 hover:bg-[#1f2937] hover:text-white transition;
}

.nav-link.active {
    @apply bg-indigo-600 text-white;
}

.alert-success {
    @apply mb-4 px-4 py-2 rounded-lg bg-green-500/10 text-green-400 border border-green-500/20;
}

.alert-error {
    @apply mb-4 px-4 py-2 rounded-lg bg-red-500/10 text-red-400 border border-red-500/20;
}
</style>

@stack('scripts')

</body>
</html>