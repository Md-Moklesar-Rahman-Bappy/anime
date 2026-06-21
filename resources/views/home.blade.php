@extends('layouts.main')

@section('title', 'Home')

@section('content')

{{-- =========================
   ✅ FEATURED HERO
========================= --}}
@if(!empty($featured) && $featured->count())
<div class="relative mb-10 rounded-xl overflow-hidden h-[420px]">

    @php $hero = $featured->first(); @endphp

    <img src="{{ $hero->banner_url ?? $hero->thumbnail_url }}"
        class="absolute inset-0 w-full h-full object-cover">

    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent"></div>

    <div class="absolute bottom-8 left-6 max-w-xl">
        <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">
            {{ $hero->title }}
        </h2>

        <p class="text-gray-300 text-sm mb-4">
            {{ \Illuminate\Support\Str::limit($hero->description, 140) }}
        </p>

        <a href="{{ route('watch', $hero->slug) }}"
           class="bg-purple-600 hover:bg-purple-500 px-5 py-2 rounded-lg text-white text-sm font-semibold transition">
            ▶ Watch Now
        </a>
    </div>

</div>
@endif


{{-- =========================
   ✅ LATEST EPISODES
========================= --}}
<section class="mb-10">
    <h2 class="text-lg font-bold mb-4">Latest Episodes</h2>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">

        @foreach($latestEpisodes ?? [] as $episode)

        <a href="{{ route('watch', $episode->anime->slug) }}"
           class="group">

            <div class="relative rounded-lg overflow-hidden bg-gray-900">

                <img src="{{ $episode->thumbnail_url }}"
                     class="w-full h-40 object-cover group-hover:scale-105 transition">

                {{-- Episode badge --}}
                <div class="absolute top-2 left-2 bg-purple-600 text-xs px-2 py-1 rounded">
                    EP {{ $episode->number }}
                </div>

                {{-- SUB / DUB --}}
                @if($episode->has_sub)
                    <div class="absolute top-2 right-2 bg-blue-600 text-xs px-2 py-1 rounded">SUB</div>
                @endif

                @if($episode->has_dub)
                    <div class="absolute top-8 right-2 bg-green-600 text-xs px-2 py-1 rounded">DUB</div>
                @endif

            </div>

            <p class="text-sm text-gray-300 mt-2 group-hover:text-white">
                {{ $episode->anime->title }}
            </p>

        </a>

        @endforeach

    </div>
</section>


{{-- =========================
   ✅ MAIN GRID (3 COLUMN)
========================= --}}
<div class="grid md:grid-cols-3 gap-6">

    {{-- NEW RELEASE --}}
    <div>
        <h3 class="text-sm font-bold mb-3">New Release</h3>

        @foreach(($newAnime ?? collect())->take(5) as $anime)
        <a href="{{ route('anime.detail', $anime->slug) }}"
           class="flex gap-3 mb-3 group">

            <img src="{{ $anime->thumbnail_url }}"
                 class="w-12 h-16 rounded object-cover">

            <div>
                <p class="text-sm text-gray-300 group-hover:text-white">
                    {{ $anime->title }}
                </p>
                <span class="text-xs text-gray-500">
                    {{ $anime->episodes_count ?? '?' }} EP
                </span>
            </div>

        </a>
        @endforeach
    </div>

    {{-- NEWLY ADDED --}}
    <div>
        <h3 class="text-sm font-bold mb-3">Newly Added</h3>

        @foreach(($newlyAdded ?? collect())->take(5) as $anime)
        <a href="{{ route('anime.detail', $anime->slug) }}"
           class="flex gap-3 mb-3 group">

            <img src="{{ $anime->thumbnail_url }}"
                 class="w-12 h-16 rounded object-cover">

            <p class="text-sm text-gray-300 group-hover:text-white">
                {{ $anime->title }}
            </p>

        </a>
        @endforeach
    </div>

    {{-- COMPLETED --}}
    <div>
        <h3 class="text-sm font-bold mb-3">Completed</h3>

        @foreach(($justCompleted ?? collect())->take(5) as $anime)
        <a href="{{ route('anime.detail', $anime->slug) }}"
           class="flex gap-3 mb-3 group">

            <img src="{{ $anime->thumbnail_url }}"
                 class="w-12 h-16 rounded object-cover">

            <p class="text-sm text-gray-300 group-hover:text-white">
                {{ $anime->title }}
            </p>

        </a>
        @endforeach
    </div>

</div>


{{-- =========================
   ✅ TOP ANIME
========================= --}}
<div class="mt-10">
    <h3 class="text-lg font-bold mb-4">Top Anime</h3>

    @foreach(($topAnime ?? []) as $i => $anime)

    <a href="{{ route('anime.detail', $anime->slug) }}"
       class="flex items-center mb-3 group">

        <span class="w-6 text-gray-500">
            {{ $i + 1 }}
        </span>

        <img src="{{ $anime->thumbnail_url }}"
             class="w-12 h-16 object-cover rounded mx-2">

        <div>
            <p class="text-sm text-gray-300 group-hover:text-white">
                {{ $anime->title }}
            </p>
            <p class="text-xs text-gray-500">
                ⭐ {{ $anime->rating ?? 'N/A' }}
            </p>
        </div>

    </a>

    @endforeach
</div>

@endsection