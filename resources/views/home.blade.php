@extends('layouts.main')

@section('title', 'Home')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    {{-- ✅ FEATURED --}}
    @if(!empty($featured) && $featured->count())
    <section class="mb-8">
        <h2 class="text-xl font-bold text-white mb-4">Featured Anime</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            @foreach($featured as $anime)
            <a href="{{ route('anime.detail', $anime->slug) }}" class="group">
                <img src="{{ $anime->thumbnail_url }}" 
                     class="rounded-lg w-full h-48 object-cover group-hover:scale-105 transition" 
                     alt="">
                <h3 class="text-sm text-gray-300 mt-2 group-hover:text-white">
                    {{ $anime->title }}
                </h3>
            </a>
            @endforeach
        </div>
    </section>
    @endif


    {{-- ✅ LATEST EPISODES --}}
    <section class="mb-10">
        <h2 class="text-lg font-bold text-white mb-4">Latest Episodes</h2>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($latestEpisodes ?? [] as $episode)
            <a href="{{ route('watch', ['slug' => $episode->anime->slug, 'ep' => $episode->number]) }}">
                <img src="{{ $episode->thumbnail_url }}" class="rounded-lg w-full h-48 object-cover">
                <p class="text-sm text-gray-300 mt-2">{{ $episode->anime->title }}</p>
                <p class="text-xs text-gray-500">Episode {{ $episode->number }}</p>
            </a>
            @endforeach
        </div>
    </section>


    {{-- ✅ 3 COLUMN SECTION --}}
    <div class="grid md:grid-cols-3 gap-6">

        {{-- ✅ NEW RELEASE --}}
        <div>
            <h3 class="text-sm font-bold text-white mb-3">New Release</h3>

            @foreach(($newAnime ?? collect())->take(5) as $anime)
            <a href="{{ route('anime.detail', $anime->slug) }}" class="flex gap-3 mb-2">
                <img src="{{ $anime->thumbnail_url }}" class="w-10 h-14 rounded">
                <p class="text-sm text-gray-300">{{ $anime->title }}</p>
            </a>
            @endforeach
        </div>

        {{-- ✅ NEWLY ADDED (FIXED) --}}
        <div>
            <h3 class="text-sm font-bold text-white mb-3">Newly Added</h3>

            {{-- ✅ USE newAnime --}}
            @foreach(($newAnime ?? []) as $anime)
            <a href="{{ route('anime.detail', $anime->slug) }}" class="flex gap-3 mb-2">
                <img src="{{ $anime->thumbnail_url }}" class="w-10 h-14 rounded">
                <p class="text-sm text-gray-300">{{ $anime->title }}</p>
            </a>
            @endforeach
        </div>

        {{-- ✅ COMPLETED (FIXED) --}}
        <div>
            <h3 class="text-sm font-bold text-white mb-3">Completed</h3>

            {{-- ✅ USE completed --}}
            @foreach(($completed ?? []) as $anime)
            <a href="{{ route('anime.detail', $anime->slug) }}" class="flex gap-3 mb-2">
                <img src="{{ $anime->thumbnail_url }}" class="w-10 h-14 rounded">
                <p class="text-sm text-gray-300">{{ $anime->title }}</p>
            </a>
            @endforeach
        </div>

    </div>


    {{-- ✅ TOP ANIME (SIDEBAR) --}}
    <div class="mt-10">
        <h3 class="text-lg font-bold text-white mb-4">Top Anime</h3>

        {{-- ✅ USE trending --}}
        @foreach(($trending ?? []) as $i => $anime)
        <div class="flex items-center mb-3">

            <span class="w-5 text-gray-500">
                {{ $i + 1 }}
            </span>

            <img src="{{ $anime->thumbnail_url }}" 
                 class="w-10 h-14 object-cover rounded mx-2">

            <div>
                <p class="text-sm text-gray-300">
                    {{ $anime->title }}
                </p>
                <p class="text-xs text-gray-500">
                    {{ $anime->rating ?? 'N/A' }}
                </p>
            </div>

        </div>
        @endforeach
    </div>

</div>
@endsection