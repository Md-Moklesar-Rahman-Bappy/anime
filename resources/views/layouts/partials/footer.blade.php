<footer style="background:#0a0a0f;border-top:1px solid rgba(55,65,81,0.6);margin-top:3rem;">
    <div class="container-xl" style="padding:2.5rem 1rem;">

        <!-- A-Z List -->
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-1 mb-4" style="font-size:0.875rem;">
            <span style="color:#9ca3af;font-weight:500;">A-Z:</span>

            <a href="{{ route('az-list', 'all') }}" class="footer-link">All</a>
            <span class="divider">|</span>

            @foreach(range('A', 'Z') as $letter)
                <a href="{{ route('az-list', $letter) }}" class="footer-link">{{ $letter }}</a>
            @endforeach

            <span class="divider">|</span>
            <a href="{{ route('az-list', '0-9') }}" class="footer-link">#</a>
        </div>

        <!-- Logo + Social -->
        <div class="d-flex flex-column align-items-center mb-4">

            <a href="{{ route('home') }}" style="font-size:1.5rem;font-weight:700;color:#6366f1;text-decoration:none;margin-bottom:0.5rem;">
                AniWaves
            </a>

            <p style="color:#6b7280;font-size:0.875rem;margin-bottom:0.75rem;">
                Join our community
            </p>

            <div class="d-flex align-items-center gap-3">

                <!-- Reddit -->
                <a href="https://reddit.com/r/animekoto" target="_blank" rel="noopener"
                   class="footer-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor" style="width:1.25rem;height:1.25rem;">
                        <path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12..." />
                    </svg>
                </a>

                <!-- Discord -->
                <a href="https://discord.gg/sKgANZEAD" target="_blank" rel="noopener"
                   class="footer-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor" style="width:1.25rem;height:1.25rem;">
                        <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885..." />
                    </svg>
                </a>

            </div>
        </div>

        <!-- Links -->
        <div class="d-flex flex-wrap justify-content-center gap-4 mb-4" style="font-size:0.875rem;">
            <a href="{{ route('home') }}" class="footer-link">Home</a>
            <a href="{{ route('contact') }}" class="footer-link">Contact</a>
            <a href="{{ route('faq') }}" class="footer-link">FAQ</a>
            <a href="{{ route('about') }}" class="footer-link">About</a>
            <a href="{{ route('dmca') }}" class="footer-link">DMCA</a>
            <a href="{{ route('terms') }}" class="footer-link">Terms</a>
        </div>

        <!-- Copyright -->
        <p style="text-align:center;color:#4b5563;font-size:0.75rem;">
            © {{ date('Y') }} AniWaves. All rights reserved.
        </p>

        <p style="text-align:center;color:#374151;font-size:0.75rem;margin-top:0.25rem;max-width:36rem;margin-left:auto;margin-right:auto;">
            This site does not store any files on its server. All contents are provided by third-party sources.
        </p>

    </div>
</footer>

<!-- Styles -->
<style>
.footer-link {
    color: #9ca3af;
    text-decoration: none;
    transition: color 0.15s;
    padding: 0 0.375rem;
}
.footer-link:hover {
    color: #818cf8;
}
.footer-icon {
    color: #9ca3af;
    transition: color 0.15s;
}
.footer-icon:hover {
    color: #818cf8;
}
.divider {
    color: #374151;
}
</style>
