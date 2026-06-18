<footer class="bg-[#0a0a0f] border-t border-gray-800/60 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">

        <!-- A-Z List -->
        <div class="flex flex-wrap items-center justify-center gap-x-2 gap-y-1 mb-8 text-sm">
            <span class="text-gray-400 font-medium">A-Z:</span>

             }}" class="footer-link">All</a>
            <span class="divider">|</span>

            @foreach(range('A', 'Z') as $letter)
                 }}" class="footer-link">{{ $letter }}</a>
            @endforeach

            <span class="divider">|</span>
             }}" class="footer-link">#</a>
        </div>

        <!-- Logo + Social -->
        <div class="flex flex-col items-center mb-6">

             }}" class="text-2xl font-bold text-indigo-500 mb-2">
                AniWaves
            </a>

            <p class="text-gray-500 text-sm mb-3">
                Join our community
            </p>

            <div class="flex items-center gap-3">

                <!-- Reddit -->
                <a href="https://reddit.com/r/animekoto" target="_blank" rel="noopener"
                   class="footer-icon">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12..." />
                    </svg>
                </a>

                <!-- Discord -->
                <a href="https://discord.gg/sKgANZEAD" target="_blank" rel="noopener"
                   class="footer-icon">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885..." />
                    </svg>
                </a>

            </div>
        </div>

        <!-- Links -->
        <div class="flex flex-wrap justify-center gap-6 mb-6 text-sm">
             }}" class="footer-link">Home</a>
             }}" class="footer-link">Contact</a>
             }}" class="footer-link">FAQ</a>
             }}" class="footer-link">About</a>
             }}" class="footer-link">DMCA</a>
             }}" class="footer-link">Terms</a>
        </div>

        <!-- Copyright -->
        <p class="text-center text-gray-600 text-xs">
            © {{ date('Y') }} AniWaves. All rights reserved.
        </p>

        <p class="text-center text-gray-700 text-xs mt-1 max-w-xl mx-auto">
            This site does not store any files on its server. All contents are provided by third-party sources.
        </p>

    </div>
</footer>

<!-- Styles -->
<style>
.footer-link {
    @apply text-gray-400 hover:text-indigo-400 transition px-1.5;
}

.footer-icon {
    @apply text-gray-400 hover:text-indigo-400 transition;
}

.divider {
    @apply text-gray-700;
}
</style>