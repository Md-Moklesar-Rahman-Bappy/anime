<footer class="bg-[#0a0a0f] border-t border-gray-700 mt-12">

    <div class="max-w-6xl mx-auto px-4 py-10">

        {{-- A-Z --}}
        <div class="flex flex-wrap justify-center items-center gap-1 text-sm mb-6 text-gray-400">
            <span>A-Z:</span>

            <a href="{{ route('az-list','all') }}">All</a>
            <span>|</span>

            @foreach(range('A','Z') as $l)
                <a href="{{ route('az-list',$l) }}">{{ $l }}</a>
            @endforeach

            <span>|</span>
            <a href="{{ route('az-list','0-9') }}">#</a>
        </div>

        {{-- LOGO --}}
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="text-2xl font-bold text-indigo-500">
                AniKoto
            </a>
        </div>

        {{-- STATIC LINKS --}}
        <div class="flex justify-center gap-5 text-sm mb-6">

            <a href="{{ route('home') }}" class="footer-link">Home</a>
            <a href="{{ route('static.page','contact') }}" class="footer-link">Contact</a>
            <a href="{{ route('static.page','faq') }}" class="footer-link">FAQ</a>
            <a href="{{ route('static.page','about') }}" class="footer-link">About</a>
            <a href="{{ route('static.page','dmca') }}" class="footer-link">DMCA</a>
            <a href="{{ route('static.page','terms') }}" class="footer-link">Terms</a>

        </div>

        {{-- COPYRIGHT --}}
        <p class="text-center text-xs text-gray-500">
            © {{ date('Y') }} AniKoto
        </p>

    </div>

</footer>