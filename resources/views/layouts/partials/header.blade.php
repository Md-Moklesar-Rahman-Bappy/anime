@php
$genres = Cache::remember('genres_list', 1800, fn() => \App\Models\Genre::all());
@endphp

<header class="bg-[#0a0a0f] border-b border-gray-800 sticky top-0 z-50"
        x-data="{ mobileNav: false }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <!-- Top Bar -->
        <div class="flex items-center justify-between h-14">

            <!-- LEFT -->
            <div class="flex items-center gap-6">

                <!-- Logo -->
                 }}" class="text-xl font-bold text-indigo-500 tracking-tight">
                    AniWaves
                </a>

                <!-- Desktop Nav -->
                <nav class="hidden lg:flex gap-1 text-sm">

                    <a href="{{ route('home') }}" class="nav-link">Home</a>

                    <!-- Genres -->
                    <div x-data="{ open:false }" class="relative">
                        <button @mouseenter="open=true" @mouseleave="open=false"
                                class="nav-link flex items-center gap-1">
                            Genres ▼
                        </button>

                        <div x-show="open" @mouseenter="open=true" @mouseleave="open=false"
                             class="dropdown-wide" x-cloak>

                            @foreach($genres->chunk(ceil($genres->count()/3)) as $chunk)
                                <div class="flex-1">
                                    @foreach($chunk as $genre)
                                         }}" class="dropdown-item">
                                            {{ $genre->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach

                        </div>
                    </div>

                    <!-- Types -->
                    <div x-data="{ open:false }" class="relative">
                        <button @mouseenter="open=true" @mouseleave="open=false"
                                class="nav-link flex items-center gap-1">
                            Types ▼
                        </button>

                        <div x-show="open" @mouseenter="open=true" @mouseleave="open=false"
                             class="dropdown-small" x-cloak>

                            @foreach(['tv','movie','ova','ona','special','music'] as $type)
                                 }}" class="dropdown-item">
                                    {{ strtoupper($type) }}
                                </a>
                            @endforeach

                        </div>
                    </div>

                    <a href="{{ route('updated') }}" class="nav-link">Updated</a>
                    <a href="{{ route('newest') }}" class="nav-link">Added</a>
                    <a href="{{ route('trending') }}" class="nav-link">Popular</a>

                </nav>
            </div>

            <!-- RIGHT -->
            <div class="flex items-center gap-3">

                <!-- Search -->
                <div class="hidden md:block relative" x-data="searchDropdown()">

                    <input type="text"
                           x-model="query"
                           @input.debounce.300ms="search"
                           @focus="open = true"
                           placeholder="Search anime..."
                           class="search-input">

                    <!-- Results -->
                    <div x-show="open && results.anime.length"
                         class="search-dropdown" x-cloak>

                        <template x-for="item in results.anime" :key="item.id">
                            <a :href="item.url" class="search-item">
                                <img :src="item.thumbnail_url" class="search-thumb">
                                <span x-text="item.title"></span>
                            </a>
                        </template>

                    </div>

                </div>

                <!-- Auth -->
                @auth
                <div x-data="{ open:false }" class="relative">

                    <button @click="open = !open">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}"
                             class="w-8 h-8 rounded-full">
                    </button>

                    <div x-show="open" @click.outside="open=false"
                         class="dropdown-account" x-cloak>

                        <p class="p-3 text-sm text-gray-400 border-b border-gray-800">
                            {{ auth()->user()->name }}
                        </p>

                         }}" class="dropdown-item">Profile</a>

                        @if(auth()->user()->isAdmin())
                             }}" class="dropdown-item">Admin</a>
                        @endif

                         }}">
                            @csrf
                            <button class="dropdown-item w-full text-left">Logout</button>
                        </form>

                    </div>
                </div>
                @else
                <button @click="$dispatch('open-login')" class="nav-link">Login</button>
                <button @click="$dispatch('open-register')" class="btn-primary">Register</button>
                @endauth

                <!-- Mobile Toggle -->
                <button class="lg:hidden text-gray-400"
                        @click="mobileNav = !mobileNav">
                    ☰
                </button>

            </div>
        </div>

        <!-- Mobile Nav -->
        <div x-show="mobileNav" class="lg:hidden space-y-1 pb-4" x-cloak>
            <a href="{{ route('home') }}" class="mobile-link">Home</a>
            <a href="{{ route('updated') }}" class="mobile-link">Updated</a>
            <a href="{{ route('trending') }}" class="mobile-link">Popular</a>
        </div>

    </div>
</header>

<!-- Styles -->
<style>
.nav-link { @apply text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5 transition; }
.dropdown-wide { @apply absolute mt-1 bg-[#141424] p-4 rounded-xl border border-gray-800 flex gap-4 z-50 min-w-[500px]; }
.dropdown-small { @apply absolute mt-1 bg-[#141424] p-3 rounded-xl border border-gray-800 z-50; }
.dropdown-item { @apply block px-3 py-1.5 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg; }

.search-input { @apply bg-[#141424] text-sm text-white px-3 py-2 rounded-lg border border-gray-800 focus:ring-indigo-500; }
.search-dropdown { @apply absolute mt-2 bg-[#141424] rounded-xl border border-gray-800 shadow-xl w-full; }
.search-item { @apply flex items-center gap-2 px-4 py-2 hover:bg-white/5; }
.search-thumb { @apply w-6 h-8 rounded object-cover; }

.dropdown-account { @apply absolute right-0 mt-2 w-48 bg-[#141424] rounded-xl border border-gray-800 shadow-xl; }

.btn-primary { @apply bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-1.5 rounded-lg; }
.mobile-link { @apply block px-3 py-2 text-gray-300 hover:text-white hover:bg-white/5 rounded-lg; }
</style>
