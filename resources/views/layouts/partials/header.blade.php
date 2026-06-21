@php
    $genres = Cache::remember('genres_list', 1800, fn() => \App\Models\Genre::all());
@endphp

<header
    x-data="{ mobileNav: false, searchOpen: false }"
    @keydown.escape.window="mobileNav = false; searchOpen = false"
    class="sticky top-0 z-50 bg-[#0a0a0f]/95 backdrop-blur-md border-b border-gray-800"
>

    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <div class="flex items-center justify-between h-16 gap-4">

            {{-- ─────── LEFT: LOGO + NAV ─────── --}}
            <div class="flex items-center gap-6 min-w-0">

                {{-- LOGO --}}
                {{ route('home') }}flex items-center gap-2 shrink-0">
                    <x-application-logo class="h-8 w-8" />
                    <span class="text-lg font-bold text-white hidden sm:inline">
                        Ani<span class="text-indigo-400">Koto</span>
                    </span>
                </a>

                {{-- DESKTOP NAV --}}
                <nav class="hidden lg:flex items-center gap-1 text-sm">

                    {{ route('home') }}
                       class="nav-item {{ request()->routeIs('home') ? 'nav-item-active' : '' }}">
                        Home
                    </a>

                    {{-- GENRES DROPDOWN --}}
                    <div x-data="{ open: false }"
                         @mouseenter="open = true"
                         @mouseleave="open = false"
                         @click.outside="open = false"
                         class="relative">

                        <button @click="open = !open" class="nav-item flex items-center gap-1">
                            Genres
                            <svg class="w-3 h-3 transition" :class="open && 'rotate-180'"
                                 viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 top-full mt-1 w-[480px] rounded-xl bg-[#0f111a] border border-gray-800 shadow-xl p-4 grid grid-cols-3 gap-1">

                            @foreach($genres as $genre)
                                {{ route('genre', $genre->slug) }}
                                   class="px-3 py-1.5 rounded text-sm text-gray-400 hover:bg-indigo-600/20 hover:text-indigo-300 transition truncate">
                                    {{ $genre->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- TYPES DROPDOWN --}}
                    <div x-data="{ open: false }"
                         @mouseenter="open = true"
                         @mouseleave="open = false"
                         @click.outside="open = false"
                         class="relative">

                        <button @click="open = !open" class="nav-item flex items-center gap-1">
                            Types
                            <svg class="w-3 h-3 transition" :class="open && 'rotate-180'"
                                 viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 top-full mt-1 w-44 rounded-xl bg-[#0f111a] border border-gray-800 shadow-xl py-2">

                            @foreach(['tv' => 'TV Series', 'movie' => 'Movie', 'ova' => 'OVA', 'ona' => 'ONA', 'special' => 'Special', 'music' => 'Music'] as $key => $label)
                                {{ route('filter', ['type' => $key]) }}
                                   class="block px-4 py-2 text-sm text-gray-400 hover:bg-indigo-600/20 hover:text-indigo-300 transition">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{ route('updated') }}
                       class="nav-item {{ request()->routeIs('updated') ? 'nav-item-active' : '' }}">
                        Updated
                    </a>

                    {{ route('newest') }}
                       class="nav-item {{ request()->routeIs('newest') ? 'nav-item-active' : '' }}">
                        Added
                    </a>

                    {{ route('trending') }}
                       class="nav-item {{ request()->routeIs('trending') ? 'nav-item-active' : '' }}">
                        Popular
                    </a>

                    @if(Route::has('manga.index'))
                        {{ route('manga.index') }}
                           class="nav-item {{ request()->routeIs('manga.*') ? 'nav-item-active' : '' }}">
                            Manga
                        </a>
                    @endif

                </nav>
            </div>

            {{-- ─────── RIGHT: SEARCH + AUTH ─────── --}}
            <div class="flex items-center gap-2 sm:gap-3">

                {{-- SEARCH (DESKTOP) --}}
                {{ route('search') }}" class="hidden md:block">
                    <div class="relative">
                        <input
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Search anime…"
                            class="w-48 lg:w-64 bg-gray-900 border border-gray-800 rounded-full text-sm pl-9 pr-3 py-1.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                        >
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                        </svg>
                    </div>
                </form>

                {{-- SEARCH ICON (MOBILE) --}}
                <button @click="searchOpen = !searchOpen"
                        class="md:hidden text-gray-400 hover:text-white transition p-1.5"
                        aria-label="Toggle search">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </button>

                {{-- AUTH --}}
                @auth
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">

                        <button @click="open = !open"
                                class="flex items-center gap-2 rounded-full hover:bg-gray-800/60 p-1 transition">
                            ?name={{ urlencode(auth()->user()->name) }}&background=6366f1&color=fff&size=32"
                                 class="w-8 h-8 rounded-full"
                                 alt="{{ auth()->user()->name }}">
                        </button>

                        <div x-show="open"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute right-0 mt-2 w-56 rounded-xl bg-[#0f111a] border border-gray-800 shadow-xl overflow-hidden">

                            {{-- USER INFO --}}
                            <div class="px-4 py-3 border-b border-gray-800">
                                <p class="text-sm font-medium text-white truncate">
                                    {{ auth()->user()->name }}
                                </p>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ auth()->user()->email }}
                                </p>
                            </div>

                            <div class="py-1">
                                {{ route('profile.edit') }}>
                                    <i class="fas fa-user w-4 text-gray-500"></i>
                                    Profile
                                </a>

                                #
                                    <i class="fas fa-bookmark w-4 text-gray-500"></i>
                                    My List
                                </a>

                                #
                                    <i class="fas fa-history w-4 text-gray-500"></i>
                                    Watch History
                                </a>

                                @if(auth()->user()->isAdmin())
                                    <div class="border-t border-gray-800 my-1"></div>
                                    {{ route('admin.dashboard') }}>
                                        <i class="fas fa-shield-alt w-4 text-indigo-400"></i>
                                        Admin Panel
                                    </a>
                                @endif
                            </div>

                            <div class="border-t border-gray-800">
                                {{ route('auth.logout') }}
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-red-400 hover:bg-gray-800 transition">
                                        <i class="fas fa-sign-out-alt w-4"></i>
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    {{ route('auth.login') }}
                       class="hidden sm:inline-flex nav-item">
                        Login
                    </a>
                    {{ route('auth.register') }}>
                        Register
                    </a>
                @endauth

                {{-- MOBILE MENU BUTTON --}}
                <button @click="mobileNav = !mobileNav"
                        class="lg:hidden text-gray-400 hover:text-white transition p-1.5"
                        aria-label="Toggle menu">
                    <svg x-show="!mobileNav" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileNav" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

            </div>
        </div>

        {{-- ─────── MOBILE SEARCH BAR ─────── --}}
        <div x-show="searchOpen" x-cloak x-transition class="md:hidden pb-3">
            {{ route('search') }}
                <div class="relative">
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search anime…"
                        autofocus
                        class="w-full bg-gray-900 border border-gray-800 rounded-lg text-sm pl-9 pr-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </div>
            </form>
        </div>

        {{-- ─────── MOBILE NAV ─────── --}}
        <div x-show="mobileNav"
             x-cloak
             x-transition
             class="lg:hidden pb-4">

            <nav class="flex flex-col gap-1 text-sm">

                {{ route('home') }} class="mobile-link {{ request()->routeIs('home') ? 'bg-indigo-600/20 text-indigo-300' : '' }}">
                    <i class="fas fa-home w-4"></i> Home
                </a>

                {{ route('updated') }} class="mobile-link">
                    <i class="fas fa-clock w-4"></i> Updated
                </a>

                {{ route('newest') }} class="mobile-link">
                    <i class="fas fa-star w-4"></i> Newly Added
                </a>

                {{ route('trending') }} class="mobile-link">
                    <i class="fas fa-fire w-4"></i> Popular
                </a>

                @if(Route::has('manga.index'))
                    {{ route('manga.index') }} class="mobile-link">
                        <i class="fas fa-book w-4"></i> Manga
                    </a>
                @endif

                {{-- Mobile genres (collapsible) --}}
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="mobile-link w-full flex items-center justify-between">
                        <span><i class="fas fa-tags w-4"></i> Genres</span>
                        <svg class="w-3 h-3 transition" :class="open && 'rotate-180'"
                             viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="ml-6 grid grid-cols-2 gap-x-3 gap-y-1 mt-1 mb-2">
                        @foreach($genres as $genre)
                            {{ route('genre', $genre->slug) }}
                               class="text-xs text-gray-400 hover:text-indigo-300 py-1">
                                {{ $genre->name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                @guest
                    <div class="border-t border-gray-800 mt-2 pt-2 flex gap-2">
                        {{ route('auth.login') }} class="flex-1 btn-cancel btn-sm justify-center">
                            Login
                        </a>
                        {{ route('auth.register') }} class="flex-1 btn-primary btn-sm justify-center">
                            Register
                        </a>
                    </div>
                @endguest

            </nav>
        </div>

    </div>
</header>