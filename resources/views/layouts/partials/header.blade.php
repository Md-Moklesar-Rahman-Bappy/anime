@php $genres = \Illuminate\Support\Facades\Cache::remember('genres_list', 1800, fn() => \App\Models\Genre::all()); @endphp
<header class="bg-[#0a0a0f] border-b border-gray-800/60 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-14">
            <div class="flex items-center space-x-6">
                <a href="{{ route('home') }}" class="text-xl font-bold text-purple-500 tracking-tight">AniWaves</a>
                <nav class="hidden lg:flex items-center space-x-1 text-sm">
                    <a href="{{ route('home') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5 transition">Home</a>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @mouseenter="open = true" class="text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5 transition flex items-center gap-1">
                            Genre <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                        </button>
                        <div x-show="open" @mouseleave="open = false" @click.outside="open = false" class="absolute left-0 mt-1 bg-[#141424] rounded-xl shadow-2xl p-4 z-50 flex gap-4 border border-gray-800/50 min-w-[500px]" x-cloak>
                            @foreach($genres->chunk(ceil($genres->count()/3)) as $chunk)
                                <div class="flex flex-col gap-0.5 flex-1">
                                    @foreach($chunk as $genre)
                                        <a href="{{ route('genre', $genre->slug) }}"
                                           class="text-sm text-gray-400 hover:text-white hover:bg-white/5 px-3 py-1.5 rounded-lg transition">{{ $genre->name }}</a>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @mouseenter="open = true" class="text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5 transition flex items-center gap-1">
                            Types <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                        </button>
                        <div x-show="open" @mouseleave="open = false" @click.outside="open = false" class="absolute left-0 mt-1 bg-[#141424] rounded-xl shadow-2xl p-3 z-50 border border-gray-800/50 min-w-[160px]" x-cloak>
                            <a href="{{ route('filter') }}?type=tv" class="block text-sm text-gray-400 hover:text-white hover:bg-white/5 px-3 py-1.5 rounded-lg transition">TV</a>
                            <a href="{{ route('filter') }}?type=movie" class="block text-sm text-gray-400 hover:text-white hover:bg-white/5 px-3 py-1.5 rounded-lg transition">Movie</a>
                            <a href="{{ route('filter') }}?type=ova" class="block text-sm text-gray-400 hover:text-white hover:bg-white/5 px-3 py-1.5 rounded-lg transition">OVA</a>
                            <a href="{{ route('filter') }}?type=ona" class="block text-sm text-gray-400 hover:text-white hover:bg-white/5 px-3 py-1.5 rounded-lg transition">ONA</a>
                            <a href="{{ route('filter') }}?type=special" class="block text-sm text-gray-400 hover:text-white hover:bg-white/5 px-3 py-1.5 rounded-lg transition">Special</a>
                            <a href="{{ route('filter') }}?type=music" class="block text-sm text-gray-400 hover:text-white hover:bg-white/5 px-3 py-1.5 rounded-lg transition">Music</a>
                        </div>
                    </div>
                    <a href="{{ route('updated') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5 transition">Updated</a>
                    <a href="{{ route('newest') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5 transition">Added</a>
                    <a href="{{ route('trending') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5 transition">Popular</a>
                    <a href="{{ route('ongoing') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5 transition">Ongoing</a>
                    <a href="{{ route('random') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5 transition">Random</a>
                </nav>
            </div>

            <div class="flex items-center space-x-3">
                <div x-data="searchDropdown()" @click.outside="open = false" class="hidden md:block relative">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" x-model="query" @input.debounce.300ms="search()" @focus="open = true" @keydown.escape="open = false" @keydown.enter="if (results.anime.length) window.location.href = results.anime[0].url" placeholder="Anime" class="bg-[#141424] text-sm text-white rounded-lg pl-9 pr-3 py-2 w-48 focus:outline-none focus:ring-1 focus:ring-purple-500 border border-gray-800/60 placeholder:text-gray-600">
                    </div>
                    <div x-show="open && (results.anime.length || results.episodes.length)" class="absolute top-full left-0 right-0 mt-2 bg-[#141424] border border-gray-800/60 rounded-xl shadow-2xl z-50 overflow-hidden" x-cloak>
                        <template x-if="results.anime.length">
                            <div>
                                <div class="px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wider bg-[#0a0a0f]">Anime</div>
                                <template x-for="item in results.anime" :key="item.id">
                                    <a :href="item.url" class="flex items-center gap-3 px-4 py-2.5 hover:bg-white/5 transition">
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
                                <div class="px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wider bg-[#0a0a0f] border-t border-gray-800/60">Episodes</div>
                                <template x-for="item in results.episodes" :key="item.id">
                                    <a :href="item.url" class="flex items-center gap-3 px-4 py-2.5 hover:bg-white/5 transition">
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
                    <div x-show="open && loading" class="absolute top-full left-0 right-0 mt-2 bg-[#141424] border border-gray-800/60 rounded-xl shadow-2xl z-50 p-4 text-center text-sm text-gray-500" x-cloak>
                        Searching...
                    </div>
                </div>

                <span class="text-xs text-gray-600 hidden md:inline cursor-default">EN</span>

                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center text-sm text-gray-300 hover:text-white">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=7c3aed&color=fff" class="w-7 h-7 rounded-full" alt="">
                        </button>
                        <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-48 bg-[#141424] rounded-xl shadow-2xl py-2 z-50 border border-gray-800/60" x-cloak>
                            <div class="px-4 py-2 text-sm text-gray-400 border-b border-gray-800/60">{{ auth()->user()->name }}</div>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5">Profile</a>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5">Admin</a>
                            @endif
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-white/5">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <button @click="$dispatch('open-login')" class="text-sm text-gray-300 hover:text-white transition">Login</button>
                    <button @click="$dispatch('open-register')" class="text-sm bg-purple-600 hover:bg-purple-700 text-white px-4 py-1.5 rounded-lg transition font-medium">Register</button>
                @endauth

                <button class="lg:hidden text-gray-400" @click="mobileNav = !mobileNav" x-data="{ mobileNav: false }">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        <div x-data="{ mobileNav: false }" x-show="mobileNav" class="lg:hidden pb-4 space-y-1" x-cloak>
            <a href="{{ route('home') }}" class="block text-sm text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5">Home</a>
            <a href="{{ route('updated') }}" class="block text-sm text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5">Updated</a>
            <a href="{{ route('newest') }}" class="block text-sm text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5">Added</a>
            <a href="{{ route('trending') }}" class="block text-sm text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5">Popular</a>
            <a href="{{ route('ongoing') }}" class="block text-sm text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5">Ongoing</a>
            <a href="{{ route('random') }}" class="block text-sm text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5">Random</a>
            <a href="{{ route('az-list') }}" class="block text-sm text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5">A-Z List</a>
            <a href="{{ route('filter') }}" class="block text-sm text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5">Filter</a>
            <a href="{{ route('manga.index') }}" class="block text-sm text-gray-300 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5">Manga</a>
        </div>
    </div>
</header>

@push('modals')
<div x-data="{ open: false }" @open-login.window="open = true" @keydown.escape="open = false" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
    <div x-show="open" @click="open = false" class="absolute inset-0 bg-black/70"></div>
    <div x-show="open" x-transition class="relative bg-[#141424] border border-gray-800/60 rounded-xl p-6 w-full max-w-sm shadow-2xl">
        <button @click="open = false" class="absolute top-3 right-3 text-gray-500 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        <h3 class="text-lg font-bold text-white mb-5">Login</h3>
        <form method="POST" action="{{ route('auth.login') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-400 block mb-1">Email</label>
                    <input type="email" name="email" required class="w-full bg-[#0a0a0f] border border-gray-800/60 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-purple-500" placeholder="your@email.com">
                </div>
                <div>
                    <label class="text-sm text-gray-400 block mb-1">Password</label>
                    <input type="password" name="password" required class="w-full bg-[#0a0a0f] border border-gray-800/60 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-purple-500" placeholder="••••••••">
                </div>
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-gray-400">
                        <input type="checkbox" name="remember" class="rounded bg-[#0a0a0f] border-gray-700 text-purple-600 focus:ring-purple-500"> Remember me
                    </label>
                    <a href="{{ route('auth.password.request') }}" class="text-purple-500 hover:text-purple-400">Forgot password?</a>
                </div>
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 rounded-lg transition">Login</button>
            </div>
        </form>
        <p class="text-center text-sm text-gray-500 mt-4">
            Don't have an account? <button @click="$dispatch('open-register'); open = false" class="text-purple-500 hover:text-purple-400">Register</button>
        </p>
    </div>
</div>

<div x-data="{ open: false }" @open-register.window="open = true" @keydown.escape="open = false" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
    <div x-show="open" @click="open = false" class="absolute inset-0 bg-black/70"></div>
    <div x-show="open" x-transition class="relative bg-[#141424] border border-gray-800/60 rounded-xl p-6 w-full max-w-sm shadow-2xl">
        <button @click="open = false" class="absolute top-3 right-3 text-gray-500 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        <h3 class="text-lg font-bold text-white mb-5">Register</h3>
        <form method="POST" action="{{ route('auth.register') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-400 block mb-1">Name</label>
                    <input type="text" name="name" required class="w-full bg-[#0a0a0f] border border-gray-800/60 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-purple-500" placeholder="Your name">
                </div>
                <div>
                    <label class="text-sm text-gray-400 block mb-1">Email</label>
                    <input type="email" name="email" required class="w-full bg-[#0a0a0f] border border-gray-800/60 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-purple-500" placeholder="your@email.com">
                </div>
                <div>
                    <label class="text-sm text-gray-400 block mb-1">Password</label>
                    <input type="password" name="password" required class="w-full bg-[#0a0a0f] border border-gray-800/60 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-purple-500" placeholder="••••••••">
                </div>
                <div>
                    <label class="text-sm text-gray-400 block mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required class="w-full bg-[#0a0a0f] border border-gray-800/60 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-purple-500" placeholder="••••••••">
                </div>
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 rounded-lg transition">Register</button>
            </div>
        </form>
        <p class="text-center text-sm text-gray-500 mt-4">
            Already have an account? <button @click="$dispatch('open-login'); open = false" class="text-purple-500 hover:text-purple-400">Sign in</button>
        </p>
    </div>
</div>
@endpush

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