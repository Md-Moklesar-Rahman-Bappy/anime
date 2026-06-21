@php
$genres = Cache::remember('genres_list', 1800, fn() => \App\Models\Genre::all());
@endphp

<header x-data="{ mobileNav:false }"
    class="sticky top-0 z-50 bg-[#0a0a0f] border-b border-gray-700">

    <div class="max-w-7xl mx-auto px-4">

        <div class="flex items-center justify-between h-14">

            {{-- LEFT --}}
            <div class="flex items-center gap-6">

                <a href="{{ route('home') }}" class="text-lg font-bold text-indigo-500">
                    AniKoto
                </a>

                <nav class="hidden lg:flex items-center gap-1 text-sm">

                    <a href="{{ route('home') }}" class="nav-link">Home</a>

                    {{-- GENRES --}}
                    <div x-data="{ open:false }" class="relative">
                        <button @mouseenter="open=true" @mouseleave="open=false"
                            class="nav-link">
                            Genres ▾
                        </button>

                        <div x-show="open" @mouseenter="open=true" @mouseleave="open=false"
                            class="dropdown-wide" x-cloak>

                            @foreach($genres->chunk(ceil($genres->count()/3)) as $chunk)
                                <div>
                                    @foreach($chunk as $genre)
                                        <a href="{{ route('genre',$genre->slug) }}" class="dropdown-item">
                                            {{ $genre->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach

                        </div>
                    </div>

                    {{-- TYPES --}}
                    <div x-data="{ open:false }" class="relative">
                        <button @mouseenter="open=true" @mouseleave="open=false"
                            class="nav-link">
                            Types ▾
                        </button>

                        <div x-show="open" @mouseenter="open=true" @mouseleave="open=false"
                            class="dropdown-small" x-cloak>

                            @foreach(['tv','movie','ova','ona','special','music'] as $type)
                                <a href="{{ route('filter',['type'=>$type]) }}" class="dropdown-item">
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

            {{-- RIGHT --}}
            <div class="flex items-center gap-3">

                {{-- AUTH --}}
                @auth
                <div x-data="{ open:false }" class="relative">

                    <button @click="open = !open">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}"
                             class="w-8 h-8 rounded-full">
                    </button>

                    <div x-show="open" @click.outside="open=false"
                         class="dropdown-account" x-cloak>

                        <p class="dropdown-title">{{ auth()->user()->name }}</p>

                        <a href="{{ route('profile.edit') }}" class="dropdown-item">Profile</a>

                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="dropdown-item">Admin</a>
                        @endif

                        <form method="POST" action="{{ route('auth.logout') }}">
                            @csrf
                            <button class="dropdown-item w-full text-left">Logout</button>
                        </form>

                    </div>
                </div>
                @else
                <a href="{{ route('auth.login') }}" class="nav-link">Login</a>
                <a href="{{ route('auth.register') }}" class="btn-primary">Register</a>
                @endauth

                {{-- MOBILE --}}
                <button @click="mobileNav = !mobileNav"
                        class="lg:hidden text-gray-400 text-xl">
                    ☰
                </button>

            </div>
        </div>

        {{-- MOBILE NAV --}}
        <div x-show="mobileNav" class="lg:hidden mt-2" x-cloak>

            <nav class="flex flex-col gap-1">
                <a href="{{ route('home') }}" class="mobile-link">Home</a>
                <a href="{{ route('updated') }}" class="mobile-link">Updated</a>
                <a href="{{ route('trending') }}" class="mobile-link">Popular</a>
            </nav>

        </div>

    </div>
</header>