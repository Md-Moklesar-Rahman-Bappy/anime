<header class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50" x-data="{ mobileOpen: false, searchOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-8">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-purple-500">AniWaves</a>
                <nav class="hidden lg:flex items-center space-x-6 text-sm">
                    <a href="{{ route('home') }}" class="text-gray-300 hover:text-white transition">Home</a>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="text-gray-300 hover:text-white transition flex items-center">Anime Genre <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></button>
                        <div x-show="open" @click.outside="open = false" class="absolute left-0 mt-2 bg-gray-800 rounded-xl shadow-xl p-5 z-50 flex gap-6 border border-gray-700/50">
                            @php $genres = \Illuminate\Support\Facades\Cache::remember('genres_list', 1800, fn() => \App\Models\Genre::all()); $mangaGenres = \Illuminate\Support\Facades\Cache::remember('manga_genres_list', 1800, fn() => \App\Models\MangaGenre::all()); @endphp
                            <div>
                                <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Anime</div>
                                @foreach($genres->chunk(8) as $chunk)
                                    <div class="flex flex-col gap-1 mb-1">
                                        @foreach($chunk as $genre)
                                            <a href="{{ route('genre', $genre->slug) }}"
                                               class="text-sm text-gray-400 hover:text-white hover:bg-gray-700/60 px-3 py-1.5 rounded-lg transition">
                                                {{ $genre->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Manga</div>
                                @foreach($mangaGenres->chunk(8) as $chunk)
                                    <div class="flex flex-col gap-1 mb-1">
                                        @foreach($chunk as $genre)
                                            <a href="{{ route('manga.genre', $genre->slug) }}"
                                               class="text-sm text-gray-400 hover:text-white hover:bg-gray-700/60 px-3 py-1.5 rounded-lg transition">
                                                {{ $genre->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="text-gray-300 hover:text-white transition flex items-center">Browse <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></button>
                        <div x-show="open" @click.outside="open = false" class="absolute left-0 mt-2 bg-gray-800 rounded-xl shadow-xl py-3 z-50 min-w-[180px] border border-gray-700/50">
                            <a href="{{ route('az-list') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700/60">A-Z List</a>
                            <a href="{{ route('filter') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700/60">Filter</a>
                            <a href="{{ route('newest') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700/60">Newest</a>
                            <a href="{{ route('updated') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700/60">Updated</a>
                            <a href="{{ route('ongoing') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700/60">Ongoing</a>
                            <a href="{{ route('trending') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700/60">Trending</a>
                            <a href="{{ route('random') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700/60">Random</a>
                            <div class="border-t border-gray-700 my-1"></div>
                            <a href="{{ route('manga.az-list') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700/60">Manga A-Z</a>
                            <a href="{{ route('manga.filter') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700/60">Manga Filter</a>
                            <a href="{{ route('manga.newest') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700/60">Manga Newest</a>
                            <a href="{{ route('manga.trending') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700/60">Manga Trending</a>
                        </div>
                    </div>
                    <a href="{{ route('manga.index') }}" class="text-gray-300 hover:text-white transition font-semibold">Manga</a>
                </nav>
            </div>

            <div class="flex items-center space-x-4">
                <form action="{{ route('filter') }}" method="GET" class="hidden md:block">
                    <input type="text" name="q" placeholder="Search anime..." class="bg-gray-800 text-sm text-white rounded-lg px-4 py-2 w-40 lg:w-48 focus:outline-none focus:ring-2 focus:ring-purple-500 border border-gray-700">
                </form>
                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center text-sm text-gray-300 hover:text-white">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=7c3aed&color=fff" class="w-8 h-8 rounded-full" alt="">
                            <span class="hidden md:inline ml-2">{{ auth()->user()->name }}</span>
                        </button>
                        <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-lg py-2 z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Profile</a>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Admin</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-300 hover:text-white">Login</a>
                    <a href="{{ route('register') }}" class="text-sm bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">Register</a>
                @endauth
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden text-gray-400 hover:text-white p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileOpen, 'inline-flex': !mobileOpen}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !mobileOpen, 'inline-flex': mobileOpen}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="mobileOpen" @click.outside="mobileOpen = false" class="lg:hidden border-t border-gray-800 bg-gray-900">
        <div class="px-4 py-3 space-y-1">
            <form action="{{ route('filter') }}" method="GET" class="mb-3">
                <input type="text" name="q" placeholder="Search anime..." class="w-full bg-gray-800 text-sm text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 border border-gray-700">
            </form>
            <a href="{{ route('home') }}" class="block px-3 py-2 text-sm text-gray-300 hover:bg-gray-800 rounded-lg">Home</a>
            <a href="{{ route('manga.index') }}" class="block px-3 py-2 text-sm text-gray-300 hover:bg-gray-800 rounded-lg">Manga</a>
            <a href="{{ route('az-list') }}" class="block px-3 py-2 text-sm text-gray-300 hover:bg-gray-800 rounded-lg">A-Z List</a>
            <a href="{{ route('filter') }}" class="block px-3 py-2 text-sm text-gray-300 hover:bg-gray-800 rounded-lg">Filter</a>
            <a href="{{ route('newest') }}" class="block px-3 py-2 text-sm text-gray-300 hover:bg-gray-800 rounded-lg">Newest</a>
            <a href="{{ route('updated') }}" class="block px-3 py-2 text-sm text-gray-300 hover:bg-gray-800 rounded-lg">Updated</a>
            <a href="{{ route('ongoing') }}" class="block px-3 py-2 text-sm text-gray-300 hover:bg-gray-800 rounded-lg">Ongoing</a>
            <a href="{{ route('trending') }}" class="block px-3 py-2 text-sm text-gray-300 hover:bg-gray-800 rounded-lg">Trending</a>
        </div>
    </div>
</header>
