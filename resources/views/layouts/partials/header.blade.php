<header class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-8">
                @php
                    $logoPath = \Illuminate\Support\Facades\Cache::remember('setting_logo', 1800, fn() => \App\Models\Setting::where('key', 'logo')->value('value'));
                    $logoUrl = $logoPath ? (\Illuminate\Support\Str::startsWith($logoPath, 'http') ? $logoPath : \Illuminate\Support\Facades\Storage::url($logoPath)) : null;
                @endphp
                <a href="{{ route('home') }}" class="flex items-center">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ config('app.name', 'AniWaves') }}" class="max-h-10">
                    @else
                        <span class="text-2xl font-bold text-purple-500">{{ config('app.name', 'AniWaves') }}</span>
                    @endif
                </a>
                <nav class="hidden md:flex items-center space-x-6 text-sm">
                    <a href="{{ route('home') }}" class="text-gray-300 hover:text-white transition">Home</a>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="text-gray-300 hover:text-white transition flex items-center">Genre <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></button>
                        <div x-show="open" @click.outside="open = false" class="absolute left-0 mt-2 bg-gray-800 rounded-xl shadow-xl p-5 z-50 flex gap-6 border border-gray-700/50">
                            @php $genres = \Illuminate\Support\Facades\Cache::remember('genres_list', 1800, fn() => \App\Models\Genre::all()); @endphp
                            @foreach($genres->chunk(10) as $chunk)
                                <div class="flex flex-col gap-1">
                                    @foreach($chunk as $genre)
                                        <a href="{{ route('genre', $genre->slug) }}"
                                           class="text-sm text-gray-400 hover:text-white hover:bg-gray-700/60 px-3 py-1.5 rounded-lg transition">
                                            {{ $genre->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <a href="{{ route('az-list') }}" class="text-gray-300 hover:text-white transition">A-Z List</a>
                    <a href="{{ route('filter') }}" class="text-gray-300 hover:text-white transition">Filter</a>
                    <a href="{{ route('newest') }}" class="text-gray-300 hover:text-white transition">Newest</a>
                    <a href="{{ route('updated') }}" class="text-gray-300 hover:text-white transition">Updated</a>
                    <a href="{{ route('ongoing') }}" class="text-gray-300 hover:text-white transition">Ongoing</a>
                    <a href="{{ route('trending') }}" class="text-gray-300 hover:text-white transition">Trending</a>
                    <a href="{{ route('random') }}" class="text-gray-300 hover:text-white transition">Random</a>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="text-gray-300 hover:text-white transition flex items-center">Manga <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></button>
                        <div x-show="open" @click.outside="open = false" class="absolute left-0 mt-2 bg-gray-800 rounded-xl shadow-xl p-4 z-50 border border-gray-700/50 min-w-[180px]">
                            <a href="{{ route('manga.index') }}" class="block text-sm text-gray-400 hover:text-white hover:bg-gray-700/60 px-3 py-2 rounded-lg transition">Home</a>
                            <a href="{{ route('manga.browse') }}" class="block text-sm text-gray-400 hover:text-white hover:bg-gray-700/60 px-3 py-2 rounded-lg transition">All Manga</a>
                            <a href="{{ route('manga.filter') }}" class="block text-sm text-gray-400 hover:text-white hover:bg-gray-700/60 px-3 py-2 rounded-lg transition">Filter</a>
                            <a href="{{ route('manga.newest') }}" class="block text-sm text-gray-400 hover:text-white hover:bg-gray-700/60 px-3 py-2 rounded-lg transition">Newest</a>
                            <a href="{{ route('manga.updated') }}" class="block text-sm text-gray-400 hover:text-white hover:bg-gray-700/60 px-3 py-2 rounded-lg transition">Updated</a>
                            <a href="{{ route('manga.ongoing') }}" class="block text-sm text-gray-400 hover:text-white hover:bg-gray-700/60 px-3 py-2 rounded-lg transition">Ongoing</a>
                            <a href="{{ route('manga.trending') }}" class="block text-sm text-gray-400 hover:text-white hover:bg-gray-700/60 px-3 py-2 rounded-lg transition">Trending</a>
                            <a href="{{ route('manga.completed') }}" class="block text-sm text-gray-400 hover:text-white hover:bg-gray-700/60 px-3 py-2 rounded-lg transition">Completed</a>
                            <a href="{{ route('manga.random') }}" class="block text-sm text-gray-400 hover:text-white hover:bg-gray-700/60 px-3 py-2 rounded-lg transition">Random</a>
                        </div>
                    </div>
                </nav>
            </div>

            <div class="flex items-center space-x-4">
                <div x-data="searchDropdown()" @click.outside="open = false" class="hidden md:block relative">
                    <input type="text" x-model="query" @input.debounce.300ms="search()" @focus="if (results.anime.length || results.episodes.length) open = true" @keydown.escape="open = false" @keydown.enter="if (results.anime.length) window.location.href = results.anime[0].url" placeholder="Search anime..." class="bg-gray-800 text-sm text-white rounded-lg px-4 py-2 w-48 focus:outline-none focus:ring-2 focus:ring-purple-500 border border-gray-700">
                    <div x-show="open && (results.anime.length || results.episodes.length)" class="absolute top-full left-0 right-0 mt-2 bg-gray-800 border border-gray-700 rounded-xl shadow-xl z-50 overflow-hidden" style="display: none;">
                        <template x-if="results.anime.length">
                            <div>
                                <div class="px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wider bg-gray-900">Anime</div>
                                <template x-for="item in results.anime" :key="item.id">
                                    <a :href="item.url" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-700/60 transition">
                                        <img :src="item.thumbnail_url" class="w-8 h-11 object-cover rounded flex-shrink-0" alt="">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm text-white truncate" x-text="item.title"></p>
                                            <p class="text-xs text-gray-500" x-text="item.type + (item.year ? ' | ' + item.year : '')"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>
                        <template x-if="results.episodes.length">
                            <div>
                                <div class="px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wider bg-gray-900 border-t border-gray-700">Episodes</div>
                                <template x-for="item in results.episodes" :key="item.id">
                                    <a :href="item.url" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-700/60 transition">
                                        <img :src="item.thumbnail_url" class="w-12 h-7 object-cover rounded flex-shrink-0" alt="">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm text-white truncate" x-text="'Ep ' + item.number + ': ' + item.title"></p>
                                            <p class="text-xs text-gray-500 truncate" x-text="item.anime_title"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div x-show="open && loading" class="absolute top-full left-0 right-0 mt-2 bg-gray-800 border border-gray-700 rounded-xl shadow-xl z-50 p-4 text-center text-sm text-gray-500">
                        Searching...
                    </div>
                </div>
                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center text-sm text-gray-300 hover:text-white">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=7c3aed&color=fff" class="w-8 h-8 rounded-full mr-2" alt="">
                            <span class="hidden md:inline">{{ auth()->user()->name }}</span>
                        </button>
                        <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-lg py-2 z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Profile</a>
                            <a href="{{ route('favorites.my-list') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">My List</a>
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
            </div>
        </div>
    </div>
</header>

@push('scripts')
<script>
    function searchDropdown() {
        return {
            query: '',
            open: false,
            loading: false,
            results: { anime: [], episodes: [] },
            search() {
                if (this.query.length < 1) {
                    this.results = { anime: [], episodes: [] };
                    this.open = false;
                    return;
                }
                this.loading = true;
                this.open = true;
                fetch('{{ route("search.ajax") }}?q=' + encodeURIComponent(this.query))
                    .then(r => r.json())
                    .then(data => {
                        this.results = data;
                        this.loading = false;
                    })
                    .catch(() => { this.loading = false; });
            }
        };
    }
</script>
@endpush
