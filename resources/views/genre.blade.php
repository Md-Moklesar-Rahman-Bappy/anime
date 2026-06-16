@extends('layouts.main')

@section('title', $genre->name . ' Anime')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-white">
                {{ $genre->name }} Anime
            </h1>
            <p class="text-sm text-gray-400 mt-1">
                Explore anime in this genre
            </p>
        </div>

        <span class="text-sm text-gray-500">
            {{ $animeList->total() }} results
        </span>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">

        @forelse($animeList as $anime)

        <a href="{{ route('anime.detail', $anime->slug) }}" class="group">

            <div class="anime-card">

                <!-- Image -->
                <img src="{{ $anime->thumbnail_url }}"
                     alt="{{ $anime->title }}"
                     class="anime-img"
                     loading="lazy">

                <!-- Overlay -->
                <div class="anime-overlay">
                    ▶ View
                </div>

                <!-- Type -->
                @if($anime->type)
                <span class="anime-type">
                    {{ $anime->type }}
                </span>
                @endif

                <!-- Episodes -->
                @if($anime->episodes_count)
                <span class="anime-ep">
                    {{ $anime->episodes_count }}
                </span>
                @endif

            </div>

            <!-- Title -->
            <h3 class="anime-title">
                {{ $anime->title }}
            </h3>

        </a>

        @empty

        <div class="col-span-full text-center text-gray-500 py-12">
            ❌ No anime found in this genre
        </div>

        @endforelse

    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $animeList->links() }}
    </div>

</div>

<style>
.anime-card {
    @apply relative rounded-xl overflow-hidden bg-[#111827] aspect-[2/3];
}

.anime-img {
    @apply w-full h-full object-cover group-hover:scale-105 transition duration-300;
}

.anime-overlay {
    @apply absolute inset-0 flex items-center justify-center bg-black/70 opacity-0 group-hover:opacity-100 text-white text-sm transition;
}

.anime-type {
    @apply absolute top-2 left-2 bg-black/70 text-xs px-2 py-1 rounded;
}

.anime-ep {
    @apply absolute top-2 right-2 bg-indigo-600 text-xs px-2 py-1 rounded;
}

.anime-title {
    @apply text-sm text-gray-300 mt-2 truncate group-hover:text-white;
}
</style>
@endsection