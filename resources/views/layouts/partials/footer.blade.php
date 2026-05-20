<footer class="bg-gray-900 border-t border-gray-800 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <div class="flex flex-wrap items-center justify-center gap-x-2 gap-y-1 mb-2">
            <span class="text-gray-400 text-sm mr-2">Anime A-Z:</span>
            @foreach(range('A', 'Z') as $letter)
                <a href="{{ route('az-list', $letter) }}" class="text-gray-400 hover:text-purple-500 text-sm px-1">{{ $letter }}</a>
            @endforeach
            <a href="{{ route('az-list', 'all') }}" class="text-gray-400 hover:text-purple-500 text-sm px-1">All</a>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-x-2 gap-y-1 mb-6">
            <span class="text-gray-400 text-sm mr-2">Manga A-Z:</span>
            @foreach(range('A', 'Z') as $letter)
                <a href="{{ route('manga.az-list', $letter) }}" class="text-gray-400 hover:text-purple-500 text-sm px-1">{{ $letter }}</a>
            @endforeach
            <a href="{{ route('manga.az-list', 'all') }}" class="text-gray-400 hover:text-purple-500 text-sm px-1">All</a>
        </div>
        <div class="flex flex-wrap justify-center gap-4 mb-6 text-sm">
            <a href="{{ route('faq') }}" class="text-gray-400 hover:text-white">FAQ</a>
            <a href="{{ route('about') }}" class="text-gray-400 hover:text-white">About</a>
            <a href="{{ route('contact') }}" class="text-gray-400 hover:text-white">Contact</a>
            <a href="{{ route('dmca') }}" class="text-gray-400 hover:text-white">DMCA</a>
            <a href="{{ route('terms') }}" class="text-gray-400 hover:text-white">Terms</a>
        </div>
        <p class="text-center text-gray-500 text-sm">&copy; {{ date('Y') }} {{ config('app.name', 'AniWaves') }}. All rights reserved.</p>
    </div>
</footer>
