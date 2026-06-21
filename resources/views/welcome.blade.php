@extends('layouts.main')

@section('title', 'Welcome')

@section('content')

<div class="min-h-screen flex flex-col items-center justify-center text-center px-4">

    {{-- TITLE --}}
    <h1 class="text-4xl font-bold mb-4">
        🎬 AniKoto
    </h1>

    <p class="text-gray-400 max-w-xl mb-6">
        Watch anime and read manga in one place.
        Fast, clean, and completely free.
    </p>

    {{-- BUTTONS --}}
    <div class="flex flex-wrap justify-center gap-3 mb-8">

        <a href="{{ route('home') }}"
           class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-lg font-semibold text-white transition">
            Watch Anime
        </a>

        <a href="{{ route('manga.index') }}"
           class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 rounded-lg font-semibold text-white transition">
            Read Manga
        </a>

        @guest
        <a href="{{ route('auth.login') }}"
           class="px-5 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg text-white transition">
            Login
        </a>
        @endguest

    </div>

    {{-- FEATURES --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl w-full">

        <div class="bg-gray-900 border border-gray-700 rounded-xl p-5">
            <div class="text-xl mb-2">🎥</div>
            <h3 class="font-semibold mb-1">High Quality Streaming</h3>
            <p class="text-gray-400 text-sm">
                Watch anime with multiple servers and HD playback.
            </p>
        </div>

        <div class="bg-gray-900 border border-gray-700 rounded-xl p-5">
            <div class="text-xl mb-2">📖</div>
            <h3 class="font-semibold mb-1">Manga Reader</h3>
            <p class="text-gray-400 text-sm">
                Smooth and fast reader with bookmarking support.
            </p>
        </div>

        <div class="bg-gray-900 border border-gray-700 rounded-xl p-5">
            <div class="text-xl mb-2">💾</div>
            <h3 class="font-semibold mb-1">Personal Lists</h3>
            <p class="text-gray-400 text-sm">
                Track your watching progress and favorites.
            </p>
        </div>

    </div>

</div>

@endsection