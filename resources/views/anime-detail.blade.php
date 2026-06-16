@extends('layouts.main')

@section('title', $anime->title)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- Banner -->
    <div class="relative rounded-2xl overflow-hidden h-[260px] md:h-[380px] mb-8">
        <img src="{{ $anime->banner_url }}" class="w-full h-full object-cover" alt="">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0f] via-black/60 to-transparent"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        <!-- LEFT -->
        <div class="space-y-4">

            <img src="{{ $anime->thumbnail_url }}"
                 class="w-full rounded-xl shadow-lg"
                 alt=""
                 loading="lazy">

            <a href="{{ route('watch', $anime->slug) }}"
               class="block w-full bg-indigo-600 hover:bg-indigo-500 text-center py-3 rounded-lg font-semibold transition">
                ▶ Watch Now
            </a>

            @auth
            <button
                x-data="{ favorited: {{ $isFavorited ? 'true' : 'false' }} }"
                @click="favorited = !favorited; 
                        fetch('/favorites/toggle', {
                            method:'POST',
                            headers:{
                                'Content-Type':'application/json',
                                'X-CSRF-TOKEN':'{{ csrf_token() }}'
                            },
                            body: JSON.stringify({anime_id: {{ $anime->id }}})
                        })"
                class="block w-full bg-[#1f2937] hover:bg-gray-700 text-white py-3 rounded-lg transition"
                x-text="favorited ? '✔ In Favorites' : '+ Add to Favorites'">
            </button>
            @endauth

        </div>

        <!-- RIGHT -->
        <div class="lg:col-span-3">

            <!-- Title -->
            <h1 class="text-3xl font-semibold text-white mb-2">
                {{ $anime->title }}
            </h1>

            <!-- Meta -->
            <div class="flex flex-wrap gap-3 text-sm text-gray-400 mb-4">

                @if($anime->rating)
                <span class="flex items-center text-yellow-400">
                    ⭐ {{ $anime->rating }}
                </span>
                @endif

                @if($anime->score)
                <span>Score: {{ $anime->score }}</span>
                @endif

                @if($anime->type)
                <span class="badge">{{ $anime->type }}</span>
                @endif

                @if($anime->status)
                <span class="badge">{{ $anime->status }}</span>
                @endif

                @if($anime->year)
                <span>{{ $anime->year }}</span>
                @endif

                @if($anime->episodes_count)
                <span>{{ $anime->episodes_count }} eps</span>
                @endif

            </div>

            <!-- Genres -->
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($anime->genres as $genre)
                 }}"
                   class="genre-tag">
                    {{ $genre->name }}
                </a>
                @endforeach
            </div>

            <!-- Description -->
            <p class="text-gray-300 leading-relaxed mb-6">
                {{ $anime->description ?? 'No description available.' }}
            </p>

            <!-- Info -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm mb-8">
                @foreach([
                    'Studio' => $anime->studio,
                    'Source' => $anime->source,
                    'Country' => $anime->country,
                    'Producers' => $anime->producers,
                    'Licensors' => $anime->licensors,
                    'Views' => $anime->views ? number_format($anime->views) : null
                ] as $label => $value)

                @if($value)
                <div>
                    <span class="text-gray-500">{{ $label }}:</span>
                    {{ $value }}
                </div>
                @endif

                @endforeach
            </div>

            <!-- Episodes -->
            @if($anime->episodes->count())
            <h2 class="section-title">Episodes</h2>

            <div class="space-y-2">
                @foreach($anime->episodes as $ep)
                 }}"
                   class="episode-card">

                    <div class="flex gap-3">
                        <span class="text-indigo-400 font-semibold">
                            Ep {{ $ep->number }}
                        </span>
                        <span>{{ $ep->title ?? 'Episode' }}</span>
                    </div>

                    <div class="flex gap-2">
                        @if($ep->has_sub)
                            <span class="badge-blue">SUB</span>
                        @endif
                        @if($ep->has_dub)
                            <span class="badge-green">DUB</span>
                        @endif
                    </div>

                </a>
                @endforeach
            </div>
            @endif

            <!-- Related -->
            @if($related->count())
            <h2 class="section-title mt-10">Related Anime</h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">

                @foreach($related as $rel)
                 }}" class="group">

                    <div class="thumbnail">
                        <img src="{{ $rel->thumbnail_url }}"
                             class="thumbnail-img"
                             loading="lazy">
                    </div>

                    <p class="thumbnail-title">
                        {{ $rel->title }}
                    </p>

                </a>
                @endforeach

            </div>
            @endif

        </div>
    </div>
</div>

<style>
.badge {
    @apply bg-[#1f2937] px-2 py-1 rounded text-gray-300;
}

.genre-tag {
    @apply bg-indigo-500/10 text-indigo-400 px-3 py-1 rounded-full text-sm hover:bg-indigo-600 hover:text-white transition;
}

.section-title {
    @apply text-xl font-semibold text-white mb-4;
}

.episode-card {
    @apply flex items-center justify-between p-3 bg-[#111827] border border-gray-800 rounded-lg hover:bg-[#1f2937] transition;
}

.badge-blue {
    @apply text-xs bg-blue-600 px-2 py-1 rounded;
}

.badge-green {
    @apply text-xs bg-green-600 px-2 py-1 rounded;
}

.thumbnail {
    @apply relative rounded-lg overflow-hidden bg-[#111827] aspect-[2/3];
}

.thumbnail-img {
    @apply w-full h-full object-cover group-hover:scale-105 transition duration-300;
}

.thumbnail-title {
    @apply text-sm text-gray-300 mt-2 truncate group-hover:text-white;
}
</style>
@endsection