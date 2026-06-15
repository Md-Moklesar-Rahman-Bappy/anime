<footer class="bg-[#0a0a0f] border-t border-gray-800/60 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        <!-- A-Z List -->
        <div class="flex flex-wrap items-center justify-center gap-x-2 gap-y-1 mb-8">
            <span class="text-gray-400 text-sm mr-1 font-medium">A-Z List:</span>
            <a href="{{ route('az-list', 'all') }}" class="text-gray-400 hover:text-purple-500 text-sm px-1.5">All</a>
            <span class="text-gray-700">|</span>
            @foreach(range('A', 'Z') as $letter)
                <a href="{{ route('az-list', $letter) }}" class="text-gray-400 hover:text-purple-500 text-sm px-1.5">{{ $letter }}</a>
            @endforeach
            <span class="text-gray-700">|</span>
            <a href="{{ route('az-list', 'other') }}" class="text-gray-400 hover:text-purple-500 text-sm px-1.5">#</a>
        </div>

        <!-- Logo + Social -->
        <div class="flex flex-col items-center mb-6">
            <a href="{{ route('home') }}" class="text-2xl font-bold text-purple-500 mb-3">AniWaves</a>
            <p class="text-gray-500 text-sm mb-3">Join now</p>
            <div class="flex items-center space-x-3">
                <a href="https://reddit.com/r/animekoto" target="_blank" rel="noopener" class="text-gray-400 hover:text-purple-500 transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.21.676-.334 1.074-.334.532 0 1.033.196 1.405.568.37.372.567.873.567 1.405 0 .532-.196 1.033-.568 1.405a1.98 1.98 0 0 1-1.404.568c-.532 0-1.033-.196-1.405-.568-.231-.23-.4-.5-.478-.816-1.19-.74-2.764-1.184-4.497-1.184-.028 0-.055.003-.082.003l-.96 4.054c.576.208 1.006.732 1.006 1.355 0 .828-.672 1.5-1.5 1.5s-1.5-.672-1.5-1.5c0-.502.246-.948.624-1.223l.964-4.068c-.535-.422-.958-1.034-1.167-1.756a2.038 2.038 0 0 1-.35.03c-1.092 0-2.096-.42-2.86-1.184-.764-.764-1.184-1.768-1.184-2.86 0-1.092.42-2.096 1.184-2.86.764-.764 1.768-1.184 2.86-1.184.532 0 1.033.196 1.405.568.372.37.567.873.567 1.405 0 .532-.196 1.033-.567 1.405a1.98 1.98 0 0 1-1.405.568c-.493 0-.96-.164-1.335-.455.406.639 1.103 1.064 1.941 1.064 1.15 0 2.096-.945 2.096-2.096 0-.87-.53-1.615-1.285-1.929l1.424-1.128a.34.34 0 0 1 .283-.048l2.702.568c.076.57.462 1.043.978 1.256z"/></svg>
                </a>
                <a href="https://discord.gg/sKgANZEAD" target="_blank" rel="noopener" class="text-gray-400 hover:text-purple-500 transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>
                </a>
            </div>
        </div>

        <!-- Help Links -->
        <div class="flex flex-wrap justify-center gap-6 mb-6 text-sm">
            <a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition">Home</a>
            <a href="{{ route('contact') }}" class="text-gray-400 hover:text-white transition">Contact</a>
            <a href="{{ route('faq') }}" class="text-gray-400 hover:text-white transition">FAQ</a>
            <a href="{{ route('about') }}" class="text-gray-400 hover:text-white transition">About</a>
            <a href="{{ route('dmca') }}" class="text-gray-400 hover:text-white transition">DMCA</a>
            <a href="{{ route('terms') }}" class="text-gray-400 hover:text-white transition">Terms</a>
        </div>

        <p class="text-center text-gray-600 text-xs">&copy; {{ date('Y') }} aniwaves. All Rights Reserved</p>
        <p class="text-center text-gray-700 text-xs mt-1">This site does not store any files on its server. All contents are provided by non-affiliated third parties.</p>
    </div>
</footer>