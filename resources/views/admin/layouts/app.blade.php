<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - {{ config('app.name', 'AniWaves') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-950 text-white">
    <div class="flex h-screen">
        <aside class="w-64 bg-gray-900 border-r border-gray-800 p-4 flex flex-col">
            <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold text-purple-500 mb-8">AniWaves Admin</a>
            <nav class="space-y-2 flex-1">
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Dashboard</a>
                <a href="{{ route('admin.anime.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.anime.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Anime</a>
                <a href="{{ route('admin.genres.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.genres.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Genres</a>
                <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.users.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Users</a>
                <a href="{{ route('admin.reports.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.reports.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Reports</a>
                <a href="{{ route('admin.requests.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.requests.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Requests</a>
                <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.settings.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Settings</a>
                <a href="{{ route('admin.jikan.search') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.jikan.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">MAL Import</a>
                <a href="{{ route('admin.scrapers.search') }}" class="block px-4 py-2 rounded-lg text-sm {{ request()->routeIs('admin.scrapers.*') ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">External Sources</a>
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
</body>
</html>
