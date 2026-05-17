<header class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-8">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-purple-500">AniWaves</a>
                <nav class="hidden md:flex items-center space-x-6 text-sm">
                    <a href="{{ route('home') }}" class="text-gray-300 hover:text-white transition">Home</a>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="text-gray-300 hover:text-white transition flex items-center">Genre <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></button>
                        <div x-show="open" @click.outside="open = false" class="absolute left-0 mt-2 bg-gray-800 rounded-xl shadow-xl p-5 z-50 flex gap-6 border border-gray-700/50">
                            @php $genres = \App\Models\Genre::all(); @endphp
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
                </nav>
            </div>

            <div class="flex items-center space-x-4">
                <form action="{{ route('filter') }}" method="GET" class="hidden md:block">
                    <input type="text" name="q" placeholder="Search anime..." class="bg-gray-800 text-sm text-white rounded-lg px-4 py-2 w-48 focus:outline-none focus:ring-2 focus:ring-purple-500 border border-gray-700">
                </form>
                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center text-sm text-gray-300 hover:text-white">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=7c3aed&color=fff" class="w-8 h-8 rounded-full mr-2" alt="">
                            <span class="hidden md:inline">{{ auth()->user()->name }}</span>
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
            </div>
        </div>
    </div>
</header>
