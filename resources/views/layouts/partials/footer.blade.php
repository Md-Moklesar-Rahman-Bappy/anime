<footer class="bg-[#0a0a0f] border-t border-gray-800 mt-16">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">

        {{-- ─────────── A-Z BROWSE ─────────── --}}
        <div class="mb-10">
            <p class="text-center text-xs uppercase tracking-wider text-gray-500 mb-3">
                Browse Anime by Letter
            </p>

            <div class="flex flex-wrap justify-center items-center gap-1.5">

                {{ route('az-list', 'all') }}
                   class="px-3 py-1.5 text-xs font-medium rounded-md bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600 hover:text-white transition">
                    All
                </a>

                @foreach(range('A','Z') as $letter)
                    {{ route('az-list', $letter) }}
                       class="w-8 h-8 flex items-center justify-center text-xs font-medium rounded-md bg-gray-900 border border-gray-800 text-gray-400 hover:bg-indigo-600 hover:text-white hover:border-indigo-500 transition">
                        {{ $letter }}
                    </a>
                @endforeach

                {{ route('az-list', '0-9') }}
                   class="w-8 h-8 flex items-center justify-center text-xs font-medium rounded-md bg-gray-900 border border-gray-800 text-gray-400 hover:bg-indigo-600 hover:text-white hover:border-indigo-500 transition">
                    #
                </a>
            </div>
        </div>

        {{-- ─────────── MAIN GRID ─────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">

            {{-- BRAND --}}
            <div class="lg:col-span-1">
                {{ route('home') }}flex items-center gap-2 mb-3">
                    <x-application-logo class="h-8 w-8" />
                    <span class="text-xl font-bold text-white">
                        Ani<span class="text-indigo-400">Koto</span>
                    </span>
                </a>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Watch and read your favorite anime and manga — anywhere, anytime. Always free.
                </p>

                {{-- SOCIAL --}}
                <div class="flex items-center gap-3 mt-4">
                    "
                       target="_blank" rel="noopener noreferrer"
                       aria-label="Discord"
                       class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-900 border border-gray-800 text-gray-400 hover:bg-indigo-600 hover:text-white hover:border-indigo-500 transition">
                        <i class="fab fa-discord"></i>
                    </a>
                    "
                       target="_blank" rel="noopener noreferrer"
                       aria-label="Twitter"
                       class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-900 border border-gray-800 text-gray-400 hover:bg-sky-500 hover:text-white hover:border-sky-500 transition">
                        <i class="fab fa-twitter"></i>
                    </a>
                    "
                       target="_blank" rel="noopener noreferrer"
                       aria-label="Facebook"
                       class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-900 border border-gray-800 text-gray-400 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    "
                       target="_blank" rel="noopener noreferrer"
                       aria-label="Reddit"
                       class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-900 border border-gray-800 text-gray-400 hover:bg-orange-600 hover:text-white hover:border-orange-600 transition">
                        <i class="fab fa-reddit-alien"></i>
                    </a>
                </div>
            </div>

            {{-- BROWSE LINKS --}}
            <div>
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">
                    Browse
                </h3>
                <ul class="space-y-2 text-sm">
                    <li>{{ route('home') }}>Home</a></li>
                    <li>#-400 hover:text-indigo-400 transition">Latest Anime</a></li>
                    <li>#text-gray-400 hover:text-indigo-400 transition">Popular</a></li>
                    <li>#000 hover:text-indigo-400 transition">Trending</a></li>
                    <li>#400 hover:text-indigo-400 transition">Genres</a></li>
                    <li>#400 hover:text-indigo-400 transition">Movies</a></li>
                </ul>
            </div>

            {{-- ACCOUNT LINKS --}}
            <div>
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">
                    Account
                </h3>
                <ul class="space-y-2 text-sm">
                    @guest
                        <li>{{ route('auth.login') }}>Login</a></li>
                        <li>{{ route('auth.register') }}>Register</a></li>
                    @endguest

                    @auth
                        <li>{{ route('profile.edit') }}>Profile</a></li>
                        <li>#hover:text-indigo-400 transition">Watch List</a></li>
                        <li>#hover:text-indigo-400 transition">History</a></li>

                        @if(auth()->user()->isAdmin() ?? false)
                            <li>{{ route('admin.dashboard') }}>Admin Panel</a></li>
                        @endif
                    @endauth
                </ul>
            </div>

            {{-- SUPPORT / LEGAL --}}
            <div>
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">
                    Support
                </h3>
                <ul class="space-y-2 text-sm">
                    <li>{{ route('static.page', 'about') }}>About</a></li>
                    <li>{{ route('static.page', 'contact') }}>Contact</a></li>
                    <li>{{ route('static.page', 'faq') }}>FAQ</a></li>
                    <li>{{ route('static.page', 'dmca') }}>DMCA</a></li>
                    <li>{{ route('static.page', 'terms') }}>Terms of Service</a></li>
                    <li>#-text-indigo-400 transition">Privacy Policy</a></li>
                </ul>
            </div>

        </div>

        {{-- ─────────── BOTTOM BAR ─────────── --}}
        <div class="pt-6 border-t border-gray-800 flex flex-col md:flex-row items-center justify-between gap-3">

            <p class="text-xs text-gray-500 text-center md:text-left">
                © {{ date('Y') }}
                <span class="text-indigo-400 font-semibold">AniKoto</span>.
                All rights reserved.
            </p>

            <p class="text-xs text-gray-600 text-center md:text-right max-w-md">
                AniKoto does not store any files on our server.
                We only link to media hosted on third-party services.
            </p>
        </div>

    </div>

</footer>