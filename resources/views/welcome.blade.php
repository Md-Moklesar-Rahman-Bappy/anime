@extends('layouts.main')

@section('title', 'Welcome')

@section('content')
<div class="min-h-screen bg-[#0a0a0f] text-white flex flex-col items-center justify-center px-4">

    <!-- Logo / Name -->
    <h1 class="text-4xl md:text-6xl font-bold mb-4 text-center">
        🎬 AniKoto
    </h1>

    <p class="text-gray-400 text-center max-w-xl mb-6">
        Watch anime and read manga in one place.
        Fast, clean, and completely free.
    </p>

    <!-- Actions -->
    <div class="flex gap-3 flex-wrap justify-center">

        <a href="{{ route('home') }}"
           class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-semibold transition">
            Watch Anime
        </a>

        <a href="{{ route('manga.home') }}"
           class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 rounded-xl font-semibold transition">
            Read Manga
        </a>

        @guest
        <a href="{{ route('auth.login') }}"
           class="px-6 py-3 bg-[#1f2937] hover:bg-gray-700 rounded-xl transition">
            Login
        </a>
        @endguest

    </div>

    <!-- Features -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12 max-w-5xl w-full">

        <div class="feature-card">
            🎥 High Quality Streaming
            <p>Watch anime with multiple servers and HD playback.</p>
        </div>

        <div class="feature-card">
            📖 Manga Reader
            <p>Smooth and fast reader with bookmarking support.</p>
        </div>

        <div class="feature-card">
            💾 Personal Lists
            <p>Track your watching progress and favorites.</p>
        </div>

    </div>

</div>

<style>
.feature-card {
    @apply bg-[#111827] border border-gray-800 rounded-xl p-5 text-center text-gray-300;
}
.feature-card p {
    @apply text-sm text-gray-500 mt-2;
}
</style>
@endsection