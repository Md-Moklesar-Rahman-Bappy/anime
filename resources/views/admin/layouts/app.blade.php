<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin - {{ config('app.name', 'AniWaves') }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    @php
        $faviconPath = Cache::remember('setting_favicon', 1800, fn() => \App\Models\Setting::where('key', 'favicon')->value('value'));
        $faviconUrl = $faviconPath && Str::startsWith($faviconPath, 'http')
            ? $faviconPath
            : ($faviconPath ? Storage::url($faviconPath) : asset('favicon.ico'));
    @endphp

    <link rel="icon" href="{{ $faviconUrl }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body style="background:#0b0e16;color:#fff;font-family:Inter,sans-serif;">

<div class="d-flex" style="height:100vh;">

    <!-- Sidebar -->
    <aside style="width:16rem;background:#111827;border-right:1px solid #374151;padding:1rem;display:flex;flex-direction:column;flex-shrink:0;"
           x-data="{ panel: localStorage.getItem('admin_panel') || 'anime' }"
           x-init="$watch('panel', val => localStorage.setItem('admin_panel', val))">

        <!-- Logo -->
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center mb-4 text-decoration-none">
            <x-application-logo style="width:2rem;height:2rem;color:#6366f1;"/>
            <span class="ms-2 fw-bold text-white">Admin</span>
        </a>

        <!-- Toggle -->
        <div class="d-flex mb-3" style="background:#1f2937;border-radius:0.5rem;padding:0.25rem;font-size:0.75rem;">
            <button @click="panel='anime'"
                :style="panel==='anime' ? 'background:#4f46e5;color:#fff' : 'color:#9ca3af'"
                class="flex-fill py-1" style="border-radius:0.25rem;border:none;">
                Anime
            </button>

            <button @click="panel='manga'"
                :style="panel==='manga' ? 'background:#059669;color:#fff' : 'color:#9ca3af'"
                class="flex-fill py-1" style="border-radius:0.25rem;border:none;">
                Manga
            </button>
        </div>

        <!-- Anime Menu -->
        <nav x-show="panel==='anime'" class="flex-grow-1">
            <div class="d-flex flex-column gap-1">

                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('admin.anime.index') }}" class="nav-link {{ request()->routeIs('admin.anime.*') ? 'active' : '' }}">Anime</a>
                <a href="{{ route('admin.featured.index') }}" class="nav-link {{ request()->routeIs('admin.featured.*') ? 'active' : '' }}">Featured</a>
                <a href="{{ route('admin.genres.index') }}" class="nav-link {{ request()->routeIs('admin.genres.*') ? 'active' : '' }}">Genres</a>
                <a href="{{ route('admin.jikan.search') }}" class="nav-link {{ request()->routeIs('admin.jikan.*') ? 'active' : '' }}">MAL Import</a>

            </div>

            <hr style="border-color:#374151;">

            <div class="d-flex flex-column gap-1">
                <a href="{{ route('admin.users.index') }}" class="nav-link">Users</a>
                <a href="{{ route('admin.comments.index') }}" class="nav-link">Comments</a>
                <a href="{{ route('admin.reports.index') }}" class="nav-link">Reports</a>
                <a href="{{ route('admin.settings.index') }}" class="nav-link">Settings</a>
            </div>

        </nav>

        <!-- Manga Menu -->
        <nav x-show="panel==='manga'" x-cloak class="flex-grow-1">
            <div class="d-flex flex-column gap-1">

                <a href="{{ route('admin.manga.dashboard') }}" class="nav-link">Dashboard</a>
                <a href="{{ route('admin.manga.index') }}" class="nav-link">Manga</a>
                <a href="{{ route('admin.manga.genres.index') }}" class="nav-link">Genres</a>

            </div>

            <hr style="border-color:#374151;">

            <div class="d-flex flex-column gap-1">
                <a href="{{ route('admin.comments.index') }}" class="nav-link">Comments</a>
                <a href="{{ route('admin.users.index') }}" class="nav-link">Users</a>
                <a href="{{ route('admin.settings.index') }}" class="nav-link">Settings</a>
            </div>

        </nav>

        <a href="{{ route('home') }}" class="mt-3 text-decoration-none" style="font-size:0.875rem;color:#6b7280;">
            ← Back to site
        </a>

    </aside>

    <!-- Content -->
    <main class="flex-grow-1 overflow-auto p-4">

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
    display: block;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    color: #9ca3af;
    text-decoration: none;
    transition: background 0.15s, color 0.15s;
}
.nav-link:hover {
    background: #1f2937;
    color: #fff;
}
.nav-link.active {
    background: #4f46e5;
    color: #fff;
}
.alert-success {
    margin-bottom: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    background: rgba(34, 197, 94, 0.1);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.2);
}
.alert-error {
    margin-bottom: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    background: rgba(239, 68, 68, 0.1);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.2);
}
</style>

@stack('scripts')

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>