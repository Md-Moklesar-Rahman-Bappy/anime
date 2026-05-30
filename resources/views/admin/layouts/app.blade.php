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
        $faviconPath = \Illuminate\Support\Facades\Cache::remember('setting_favicon', 1800, fn() => \App\Models\Setting::where('key', 'favicon')->value('value'));
        $faviconUrl = $faviconPath ? (\Illuminate\Support\Str::startsWith($faviconPath, 'http') ? $faviconPath : \Illuminate\Support\Facades\Storage::url($faviconPath)) : null;
    @endphp
    <link rel="icon" type="image/x-icon" href="{{ $faviconUrl ?: asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-950 text-white">
    <div class="flex h-screen">
        <aside class="w-64 bg-gray-900 border-r border-gray-800 p-4 flex flex-col" x-data="{ panel: localStorage.getItem('admin_panel') || '{{ request()->routeIs('admin.manga.dashboard') || request()->routeIs('admin.manga.*') && !request()->routeIs('admin.manga.genres.*') ? 'manga' : 'anime' }}' }" x-init="$watch('panel', val => localStorage.setItem('admin_panel', val))">
            @php
                $logoPath = \Illuminate\Support\Facades\Cache::remember('setting_logo', 1800, fn() => \App\Models\Setting::where('key', 'logo')->value('value'));
                $logoUrl = $logoPath ? (\Illuminate\Support\Str::startsWith($logoPath, 'http') ? $logoPath : \Illuminate\Support\Facades\Storage::url($logoPath)) : null;
            @endphp
            <div class="flex items-center justify-between mb-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ config('app.name', 'AniWaves') }}" class="max-h-8">
                    @else
                        <span class="text-lg font-bold text-purple-500">AniWaves</span>
                    @endif
                </a>
            </div>
            <div class="mb-4">
                <div class="flex bg-gray-800 rounded-lg p-1 text-xs">
                    <button @click="panel = 'anime'" :class="panel === 'anime' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-400 hover:text-white'" class="flex-1 py-1.5 rounded-md font-medium transition-all">Anime</button>
                    <button @click="panel = 'manga'" :class="panel === 'manga' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-400 hover:text-white'" class="flex-1 py-1.5 rounded-md font-medium transition-all">Manga</button>
                </div>
            </div>
            <nav class="space-y-2 flex-1" x-show="panel === 'anime'">
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Dashboard</a>
                <a href="{{ route('admin.anime.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.anime.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Anime</a>
                <a href="{{ route('admin.featured.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.featured.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Featured Slider</a>
                <a href="{{ route('admin.genres.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.genres.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Genres</a>
                <a href="{{ route('admin.jikan.search') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.jikan.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">MAL Import</a>
                <div class="border-t border-gray-800 my-2 pt-2">
                    <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.users.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Users</a>
                    <a href="{{ route('admin.comments.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.comments.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Comments</a>
                    <a href="{{ route('admin.reports.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.reports.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Reports</a>
                    <a href="{{ route('admin.requests.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.requests.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Requests</a>
                    <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.settings.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Settings</a>
                </div>
            </nav>
            <nav class="space-y-2 flex-1" x-show="panel === 'manga'" x-cloak>
                <a href="{{ route('admin.manga.dashboard') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.manga.dashboard') ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Dashboard</a>
                <a href="{{ route('admin.manga.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.manga.*') && !request()->routeIs('admin.manga.dashboard') && !request()->routeIs('admin.manga.genres.*') ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Manga</a>
                <a href="{{ route('admin.manga.genres.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.manga.genres.*') ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Manga Genres</a>
                <div class="border-t border-gray-800 my-2 pt-2">
                    <a href="{{ route('admin.comments.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.comments.*') ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Comments</a>
                    <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.users.*') ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Users</a>
                    <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.settings.*') ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Settings</a>
                </div>
            </nav>
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-white mt-4">Back to Site</a>
        </aside>
        <main class="flex-1 overflow-y-auto p-6">
            @if(session('success'))
            <div class="bg-green-600 text-white px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="bg-red-600 text-white px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
