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

<div class="flex h-screen">

    {{-- SIDEBAR --}}
    <aside class="w-64 bg-gray-900 border-r border-gray-700 p-4 flex flex-col"
           x-data="{ panel: localStorage.getItem('admin_panel') || 'anime' }"
           x-init="$watch('panel', val => localStorage.setItem('admin_panel', val))">

        {{-- LOGO --}}
         }}" class="flex items-center gap-2 mb-6 text-indigo-500 font-bold text-lg">
            🎬 Admin
        </a>

        {{-- SWITCH --}}
        <div class="flex bg-gray-800 rounded-lg p-1 text-xs mb-4">
            <button @click="panel='anime'"
                :class="panel==='anime' ? 'bg-indigo-600 text-white' : 'text-gray-400'"
                class="flex-1 py-1 rounded">
                Anime
            </button>
            <button @click="panel='manga'"
                :class="panel==='manga' ? 'bg-emerald-600 text-white' : 'text-gray-400'"
                class="flex-1 py-1 rounded">
                Manga
            </button>
        </div>

        {{-- ANIME NAV --}}
        <nav x-show="panel==='anime'" class="space-y-1 flex-1">

             }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>

             }}" class="nav-link {{ request()->routeIs('admin.anime.*') ? 'active' : '' }}">Anime</a>

             }}" class="nav-link {{ request()->routeIs('admin.featured.*') ? 'active' : '' }}">Featured</a>

             }}" class="nav-link {{ request()->routeIs('admin.genres.*') ? 'active' : '' }}">Genres</a>

             }}" class="nav-link">MAL Import</a>

            <hr class="border-gray-700 my-3">

             }}" class="nav-link">Users</a>
             }}" class="nav-link">Comments</a>
             }}" class="nav-link">Reports</a>
             }}" class="nav-link">Settings</a>

        </nav>

        {{-- MANGA NAV --}}
        <nav x-show="panel==='manga'" x-cloak class="space-y-1 flex-1">

             }}" class="nav-link">Dashboard</a>
             }}" class="nav-link">Manga</a>
             }}" class="nav-link">Genres</a>

            <hr class="border-gray-700 my-3">

             }}" class="nav-link">Comments</a>
             }}" class="nav-link">Users</a>
             }}" class="nav-link">Settings</a>

        </nav>

         }}" class="mt-4 text-sm text-gray-500 hover:text-white">
            ← Back to site
        </a>

    </aside>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 overflow-auto p-6">

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')

    </main>

</div>

{{-- Tailwind helpers --}}
<style>
.nav-link {
    @apply block px-3 py-2 rounded text-sm text-gray-400 hover:text-white hover:bg-gray-800 transition;
}
.nav-link.active {
    @apply bg-indigo-600 text-white;
}

.alert-success {
    @apply mb-4 px-4 py-2 rounded bg-green-500/10 text-green-400 border border-green-500/20;
}
.alert-error {
    @apply mb-4 px-4 py-2 rounded bg-red-500/10 text-red-400 border border-red-500/20;
}
</style>

@stack('scripts')

</body>
</html>
