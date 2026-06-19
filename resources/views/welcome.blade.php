@extends('layouts.main')

@section('title', 'Welcome')

@section('content')
<div style="min-height:100vh;background:#0a0a0f;color:#fff" class="d-flex flex-column align-items-center justify-content-center px-3">

    <h1 style="font-size:2.5rem;font-weight:700;margin-bottom:1rem;text-align:center">
        🎬 AniKoto
    </h1>

    <p style="color:#9ca3af;text-align:center;max-width:36rem;margin-bottom:1.5rem">
        Watch anime and read manga in one place.
        Fast, clean, and completely free.
    </p>

    <div class="d-flex gap-2 flex-wrap justify-content-center">

        <a href="{{ route('home') }}"
           class="btn" style="background:#4f46e5;color:#fff;border-radius:0.75rem;font-weight:600">
            Watch Anime
        </a>

        <a href="{{ route('manga.index') }}"
           class="btn" style="background:#059669;color:#fff;border-radius:0.75rem;font-weight:600">
            Read Manga
        </a>

        @guest
        <a href="{{ route('auth.login') }}"
           class="btn" style="background:#1f2937;color:#fff;border-radius:0.75rem">
            Login
        </a>
        @endguest

    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4 mt-4" style="max-width:64rem;width:100%">

        <div class="col">
            <div style="background:#111827;border:1px solid #374151;border-radius:0.75rem;padding:1.25rem;text-align:center;color:#d1d5db">
                🎥 High Quality Streaming
                <p style="color:#6b7280;font-size:0.875rem;margin-top:0.5rem">Watch anime with multiple servers and HD playback.</p>
            </div>
        </div>

        <div class="col">
            <div style="background:#111827;border:1px solid #374151;border-radius:0.75rem;padding:1.25rem;text-align:center;color:#d1d5db">
                📖 Manga Reader
                <p style="color:#6b7280;font-size:0.875rem;margin-top:0.5rem">Smooth and fast reader with bookmarking support.</p>
            </div>
        </div>

        <div class="col">
            <div style="background:#111827;border:1px solid #374151;border-radius:0.75rem;padding:1.25rem;text-align:center;color:#d1d5db">
                💾 Personal Lists
                <p style="color:#6b7280;font-size:0.875rem;margin-top:0.5rem">Track your watching progress and favorites.</p>
            </div>
        </div>

    </div>

</div>
@endsection