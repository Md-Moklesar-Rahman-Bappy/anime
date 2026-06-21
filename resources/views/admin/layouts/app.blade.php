<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') · {{ config('app.name', 'AniKoto') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

<div
    class="flex h-screen overflow-hidden"
    x-data="{
        panel: localStorage.getItem('admin_panel') || 'anime',
        sidebarOpen: false,
        userMenu: false,
    }"
    x-init="$watch('panel', val => localStorage.setItem('admin_panel', val))"
>

    {{-- ───────── SIDEBAR ───────── --}}
    <aside
        class="fixed lg:relative z-40 inset-y-0 left-0 w-64 bg-[#0f111a] border-r border-gray-800
               flex flex-col transition-transform duration-200
               -translate-x-full lg:translate-x-0"
        :class="sidebarOpen && '!translate-x-0'"
    >

        {{-- LOGO --}}
        <div class="flex items-center justify-between px-4 h-14 border-b border-gray-800">
            {{ route('admin.dashboard') }}
               class="flex items-center gap-2 text-indigo-400 font-bold text-lg">
                <span>🎬</span> AniKoto
            </a>

            <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- PANEL SWITCH --}}
        <div class="flex bg-gray-800/60 rounded-lg p-1 text-xs mx-4 mt-4">
            <button @click="panel='anime'"
                :class="panel==='anime' ? 'bg-indigo-600 text-white shadow' : 'text-gray-400 hover:text-white'"
                class="flex-1 py-1.5 rounded transition">
                Anime
            </button>
            <button @click="panel='manga'"
                :class="panel==='manga' ? 'bg-emerald-600 text-white shadow' : 'text-gray-400 hover:text-white'"
                class="flex-1 py-1.5 rounded transition">
                Manga
            </button>
        </div>

        {{-- ANIME NAV --}}
        <nav x-show="panel==='anime'" x-transition class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

            <x-admin.nav-link :href="route('admin.dashboard')"
                              :active="request()->routeIs('admin.dashboard')"
                              icon="fa-chart-line">
                Dashboard
            </x-admin.nav-link>

            <x-admin.nav-link :href="route('admin.anime.index')"
                              :active="request()->routeIs('admin.anime.*')"
                              icon="fa-film">
                Anime
            </x-admin.nav-link>

            <x-admin.nav-link :href="route('admin.featured.index')"
                              :active="request()->routeIs('admin.featured.*')"
                              icon="fa-star">
                Featured
            </x-admin.nav-link>

            <x-admin.nav-link :href="route('admin.genres.index')"
                              :active="request()->routeIs('admin.genres.*')"
                              icon="fa-tags">
                Genres
            </x-admin.nav-link>

            <x-admin.nav-link :href="route('admin.jikan.search')"
                              :active="request()->routeIs('admin.jikan.*')"
                              icon="fa-cloud-download-alt">
                MAL Import
            </x-admin.nav-link>

            <p class="px-3 mt-5 mb-2 text-[10px] uppercase tracking-wider text-gray-600 font-semibold">
                System
            </p>

            <x-admin.nav-link :href="route('admin.users.index')"
                              :active="request()->routeIs('admin.users.*')"
                              icon="fa-users">Users</x-admin.nav-link>

            <x-admin.nav-link :href="route('admin.comments.index')"
                              :active="request()->routeIs('admin.comments.*')"
                              icon="fa-comments">Comments</x-admin.nav-link>

            <x-admin.nav-link :href="route('admin.reports.index')"
                              :active="request()->routeIs('admin.reports.*')"
                              icon="fa-flag">Reports</x-admin.nav-link>

            <x-admin.nav-link :href="route('admin.settings.index')"
                              :active="request()->routeIs('admin.settings.*')"
                              icon="fa-cog">Settings</x-admin.nav-link>

        </nav>

        {{-- MANGA NAV --}}
        <nav x-show="panel==='manga'" x-cloak x-transition class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

            <x-admin.nav-link :href="route('admin.manga.dashboard')"
                              :active="request()->routeIs('admin.manga.dashboard')"
                              icon="fa-chart-line">
                Dashboard
            </x-admin.nav-link>

            <x-admin.nav-link :href="route('admin.manga.index')"
                              :active="request()->routeIs('admin.manga.index') || request()->routeIs('admin.manga.create') || request()->routeIs('admin.manga.edit')"
                              icon="fa-book">
                Manga
            </x-admin.nav-link>

            <x-admin.nav-link :href="route('admin.manga.genres.index')"
                              :active="request()->routeIs('admin.manga.genres.*')"
                              icon="fa-tags">
                Genres
            </x-admin.nav-link>

            <p class="px-3 mt-5 mb-2 text-[10px] uppercase tracking-wider text-gray-600 font-semibold">
                System
            </p>

            <x-admin.nav-link :href="route('admin.comments.index')"
                              :active="request()->routeIs('admin.comments.*')"
                              icon="fa-comments">Comments</x-admin.nav-link>

            <x-admin.nav-link :href="route('admin.users.index')"
                              :active="request()->routeIs('admin.users.*')"
                              icon="fa-users">Users</x-admin.nav-link>

            <x-admin.nav-link :href="route('admin.settings.index')"
                              :active="request()->routeIs('admin.settings.*')"
                              icon="fa-cog">Settings</x-admin.nav-link>

        </nav>

        {{-- BACK TO SITE --}}
        <div class="border-t border-gray-800 p-3">
            {{ route('home') }}
               class="flex items-center gap-2 px-3 py-2 rounded text-sm text-gray-500 hover:text-white hover:bg-gray-800 transition">
                <i class="fas fa-arrow-left"></i>
                Back to site
            </a>
        </div>

    </aside>

    {{-- MOBILE OVERLAY --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-black/60 lg:hidden"
    ></div>

    {{-- ───────── MAIN ───────── --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- TOPBAR --}}
        <header class="h-14 bg-[#0f111a] border-b border-gray-800 flex items-center justify-between px-4 lg:px-6 flex-shrink-0">

            {{-- LEFT --}}
            <div class="flex items-center gap-3">
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden text-gray-400 hover:text-white"
                >
                    <i class="fas fa-bars text-lg"></i>
                </button>

                <h1 class="text-sm font-medium text-gray-300">
                    @yield('title', 'Dashboard')
                </h1>
            </div>

            {{-- RIGHT --}}
            <div class="flex items-center gap-3">

                {{-- Quick Search (placeholder) --}}
                <button class="hidden md:flex items-center gap-2 text-xs text-gray-500 hover:text-white px-3 py-1.5 rounded-md bg-gray-800/60 border border-gray-700">
                    <i class="fas fa-search"></i>
                    Search…
                    <span class="ml-2 text-[10px] px-1.5 py-0.5 bg-gray-700 rounded">⌘K</span>
                </button>

                {{-- USER MENU --}}
                <div class="relative" @click.outside="userMenu = false">
                    <button
                        @click="userMenu = !userMenu"
                        class="flex items-center gap-2 hover:bg-gray-800/70 rounded-lg px-2 py-1.5 transition"
                    >
                        <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-semibold text-white">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <span class="hidden sm:inline text-xs text-gray-300">
                            {{ auth()->user()->name ?? 'Admin' }}
                        </span>
                        <i class="fas fa-chevron-down text-[10px] text-gray-500"></i>
                    </button>

                    <div
                        x-show="userMenu"
                        x-cloak
                        x-transition
                        class="absolute right-0 mt-2 w-48 rounded-lg bg-[#0f111a] border border-gray-800 shadow-xl py-1 text-sm"
                    >
                        {{ route('profile.edit') }}
                           class="flex items-center gap-2 px-3 py-2 text-gray-300 hover:bg-gray-800">
                            <i class="fas fa-user text-gray-500"></i> Profile
                        </a>
                        {{ route('admin.settings.index') }}
                           class="flex items-center gap-2 px-3 py-2 text-gray-300 hover:bg-gray-800">
                            <i class="fas fa-cog text-gray-500"></i> Settings
                        </a>
                        <div class="border-t border-gray-800 my-1"></div>
                        {{ route('auth.logout') }}
                            @csrf
                            <button type="submit"
                                    class="w-full text-left flex items-center gap-2 px-3 py-2 text-red-400 hover:bg-gray-800">
                                <i class="fas fa-sign-out-alt"></i> Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6 space-y-6">
            @yield('content')
        </main>
    </div>

</div>

{{-- ───────── TOAST CENTER (queue + types + actions) ───────── --}}
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
                    <button type="button"
                            x-show="t.actionLabel"
                            @click="runAction(t.id)"
                            class="text-xs font-semibold underline hover:no-underline"
                            x-text="t.actionLabel"></button>

                    <button type="button"
                            x-show="t.dismissLabel"
                            @click="dismiss(t.id)"
                            class="text-xs opacity-70 hover:opacity-100"
                            x-text="t.dismissLabel"></button>
                </div>
            </div>

            <button type="button"
                    @click="dismiss(t.id)"
                    class="opacity-60 hover:opacity-100 text-sm"
                    aria-label="Close">
                ✕
            </button>
        </div>
    </template>
</div>

{{-- Flash messages → push to toast --}}
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