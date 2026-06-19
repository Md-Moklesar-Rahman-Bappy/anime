@php
$genres = Cache::remember('genres_list', 1800, fn() => \App\Models\Genre::all());
@endphp

<header style="background:#0a0a0f;border-bottom:1px solid #374151;position:sticky;top:0;z-index:50;"
        x-data="{ mobileNav: false }">

    <div class="container-xl" style="padding:0 1rem;">

        <!-- Top Bar -->
        <div class="d-flex align-items-center justify-content-between" style="height:3.5rem;">

            <!-- LEFT -->
            <div class="d-flex align-items-center gap-4">

                <!-- Logo -->
                <a href="{{ route('home') }}" style="font-size:1.25rem;font-weight:700;color:#6366f1;text-decoration:none;letter-spacing:-0.025em;">
                    AniWaves
                </a>

                <!-- Desktop Nav -->
                <nav class="d-none d-lg-flex gap-1" style="font-size:0.875rem;">

                    <a href="{{ route('home') }}" class="nav-link">Home</a>

                    <!-- Genres -->
                    <div x-data="{ open:false }" style="position:relative;">
                        <button @mouseenter="open=true" @mouseleave="open=false"
                                class="nav-link" style="display:flex;align-items:center;gap:0.25rem;">
                            Genres ▼
                        </button>

                        <div x-show="open" @mouseenter="open=true" @mouseleave="open=false"
                             class="dropdown-wide" x-cloak>

                            @foreach($genres->chunk(ceil($genres->count()/3)) as $chunk)
                                <div style="flex:1;">
                                    @foreach($chunk as $genre)
                                        <a href="{{ route('genre', $genre->slug) }}" class="dropdown-item">
                                            {{ $genre->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach

                        </div>
                    </div>

                    <!-- Types -->
                    <div x-data="{ open:false }" style="position:relative;">
                        <button @mouseenter="open=true" @mouseleave="open=false"
                                class="nav-link" style="display:flex;align-items:center;gap:0.25rem;">
                            Types ▼
                        </button>

                        <div x-show="open" @mouseenter="open=true" @mouseleave="open=false"
                             class="dropdown-small" x-cloak>

                            @foreach(['tv','movie','ova','ona','special','music'] as $type)
                                                <a href="{{ route('filter', ['type' => $type]) }}" class="dropdown-item">
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
            <div class="d-flex align-items-center gap-3">

                <!-- Search -->
                <div class="d-none d-md-block position-relative" x-data="searchDropdown()">

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
                <div x-data="{ open:false }" style="position:relative;">

                    <button @click="open = !open" style="background:none;border:none;padding:0;">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}"
                             style="width:2rem;height:2rem;border-radius:50%;">
                    </button>

                    <div x-show="open" @click.outside="open=false"
                         class="dropdown-account" x-cloak>

                        <p style="padding:0.75rem;font-size:0.875rem;color:#9ca3af;border-bottom:1px solid #374151;margin:0;">
                            {{ auth()->user()->name }}
                        </p>

                        <a href="{{ route('profile.edit') }}" class="dropdown-item">Profile</a>

                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="dropdown-item">Admin</a>
                        @endif

                        <form method="POST" action="{{ route('auth.logout') }}">
                            @csrf
                            <button class="dropdown-item" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;">Logout</button>
                        </form>

                    </div>
                </div>
                @else
                <button @click="$dispatch('open-login')" class="nav-link">Login</button>
                <button @click="$dispatch('open-register')" class="btn-primary">Register</button>
                @endauth

                <!-- Mobile Toggle -->
                <button class="d-lg-none" style="color:#9ca3af;background:none;border:none;font-size:1.5rem;"
                        @click="mobileNav = !mobileNav">
                    ☰
                </button>

            </div>
        </div>

        <!-- Mobile Nav -->
        <div x-show="mobileNav" class="d-lg-none" style="padding-bottom:1rem;" x-cloak>
            <div class="d-flex flex-column gap-1">
                <a href="{{ route('home') }}" class="mobile-link">Home</a>
                <a href="{{ route('updated') }}" class="mobile-link">Updated</a>
                <a href="{{ route('trending') }}" class="mobile-link">Popular</a>
            </div>
        </div>

    </div>
</header>

<!-- Styles -->
<style>
.nav-link { color:#d1d5db; text-decoration:none; padding:0.5rem 0.75rem; border-radius:0.5rem; transition:background 0.15s,color 0.15s; display:inline-block; }
.nav-link:hover { color:#fff; background:rgba(255,255,255,0.05); }
.dropdown-wide { position:absolute; margin-top:0.25rem; background:#141424; padding:1rem; border-radius:0.75rem; border:1px solid #374151; display:flex; gap:1rem; z-index:50; min-width:500px; }
.dropdown-small { position:absolute; margin-top:0.25rem; background:#141424; padding:0.75rem; border-radius:0.75rem; border:1px solid #374151; z-index:50; }
.dropdown-item { display:block; padding:0.375rem 0.75rem; font-size:0.875rem; color:#9ca3af; border-radius:0.5rem; text-decoration:none; transition:background 0.15s,color 0.15s; }
.dropdown-item:hover { color:#fff; background:rgba(255,255,255,0.05); }

.search-input { background:#141424; font-size:0.875rem; color:#fff; padding:0.5rem 0.75rem; border-radius:0.5rem; border:1px solid #374151; outline:none; width:100%; }
.search-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,0.3); }
.search-dropdown { position:absolute; margin-top:0.5rem; background:#141424; border-radius:0.75rem; border:1px solid #374151; box-shadow:0 10px 30px rgba(0,0,0,0.5); width:100%; z-index:50; }
.search-item { display:flex; align-items:center; gap:0.5rem; padding:0.5rem 1rem; text-decoration:none; color:#d1d5db; }
.search-item:hover { background:rgba(255,255,255,0.05); }
.search-thumb { width:1.5rem; height:2rem; border-radius:0.25rem; object-fit:cover; }

.dropdown-account { position:absolute; right:0; margin-top:0.5rem; width:12rem; background:#141424; border-radius:0.75rem; border:1px solid #374151; box-shadow:0 10px 30px rgba(0,0,0,0.5); z-index:50; }

.btn-primary { background:#4f46e5; color:#fff; padding:0.375rem 1rem; border-radius:0.5rem; border:none; cursor:pointer; transition:background 0.15s; }
.btn-primary:hover { background:#6366f1; }
.mobile-link { display:block; padding:0.5rem 0.75rem; color:#d1d5db; border-radius:0.5rem; text-decoration:none; transition:background 0.15s,color 0.15s; }
.mobile-link:hover { color:#fff; background:rgba(255,255,255,0.05); }
</style>
