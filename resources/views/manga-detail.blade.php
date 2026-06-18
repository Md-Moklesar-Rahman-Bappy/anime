@extends('layouts.main')

@section('title', $manga->title)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- Banner -->
    <div class="relative rounded-2xl overflow-hidden h-[260px] md:h-[380px] mb-8">
        <img src="{{ $manga->banner_url ?? asset('fallback.jpg') }}" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0f] via-black/60 to-transparent"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        <!-- LEFT -->
        <div class="space-y-4">

            <img src="{{ $manga->thumbnail_url }}" class="w-full rounded-xl shadow-lg" loading="lazy">

            <a href="{{ route('manga.read', $manga->slug) }}"
               class="block w-full bg-indigo-600 hover:bg-indigo-500 text-white py-3 rounded-lg font-semibold text-center transition">
                📖 Read Now
            </a>

            @auth
            <button
                x-data="{ favorited: {{ $isFavorited ? 'true' : 'false' }} }"
                @click="favorited = !favorited;
                        fetch('/manga/favorites/toggle', {
                            method: 'POST',
                            headers: {
                                'Content-Type':'application/json',
                                'X-CSRF-TOKEN':'{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ manga_id: {{ $manga->id }} })
                        })"
                class="bg-[#1f2937] hover:bg-gray-700 text-white py-3 rounded-lg transition w-full"
                x-text="favorited ? '✔ In Favorites' : '+ Add to Favorites'">
            </button>
            @endauth

        </div>

        <!-- RIGHT -->
        <div class="lg:col-span-3">

            <!-- Title -->
            <h1 class="text-3xl font-semibold text-white mb-2">
                {{ $manga->title }}
            </h1>

            <!-- Meta -->
            <div class="flex flex-wrap gap-3 text-sm text-gray-400 mb-4">

                @if($manga->rating)
                <span class="text-yellow-400">⭐ {{ $manga->rating }}</span>
                @endif

                @if($manga->score)
                <span>Score: {{ $manga->score }}</span>
                @endif

                @if($manga->type)
                <span class="badge">{{ $manga->type }}</span>
                @endif

                @if($manga->status)
                <span class="badge">{{ $manga->status }}</span>
                @endif

                @if($manga->year)
                <span>{{ $manga->year }}</span>
                @endif

                @if($manga->chapters_count)
                <span>{{ $manga->chapters_count }} chapters</span>
                @endif

            </div>

            <!-- Genres -->
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($manga->genres as $genre)
                    <a href="{{ route('manga.genre', $genre->slug)" class="genre-tag">
                        {{ $genre->name }}
                    </a>
                @endforeach
            </div>

            <!-- Description -->
            <p class="text-gray-300 leading-relaxed mb-6">
                {{ $manga->description ?? 'No description available.' }}
            </p>

            <!-- Info -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm mb-8">
                @foreach([
                    'Author'=>$manga->author,
                    'Artist'=>$manga->artist,
                    'Publisher'=>$manga->publisher,
                    'Source'=>$manga->source,
                    'Views'=>$manga->views ? number_format($manga->views):null
                ] as $label=>$value)
                    @if($value)
                        <div><span class="text-gray-500">{{ $label }}:</span> {{ $value }}</div>
                    @endif
                @endforeach
            </div>

            <!-- Chapters -->
            @if($manga->chapters->count())
            <h2 class="section-title">Chapters</h2>

            <div class="space-y-2">
                @foreach($manga->chapters as $ch)

                <a href="{{ route('manga.read', ['slug'=>$manga->slug,'chapter'=>$ch->number]) }}"
                   class="chapter-card">

                    <div>
                        <span class="text-indigo-400 font-semibold">
                            Ch. {{ rtrim(rtrim($ch->number,'0'),'.') }}
                        </span>

                        @if($ch->title)
                        <span class="text-gray-400 ml-2">
                            {{ $ch->title }}
                        </span>
                        @endif
                    </div>

                    <span class="text-xs text-gray-500">
                        {{ $ch->pages_count }} pages
                    </span>

                </a>

                @endforeach
            </div>
            @endif

            <!-- Related -->
            @if($related->count())
            <h2 class="section-title mt-10">Related Manga</h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">

                @foreach($related as $rel)
                <a href="{{ route('manga.detail',$rel->slug) }}" class="group">

                    <div class="anime-card">
                        <img src="{{ $rel->thumbnail_url }}" class="anime-img">
                    </div>

                    <p class="anime-title">{{ $rel->title }}</p>

                </a>
                @endforeach

            </div>
            @endif

        </div>
    </div>
</div>

<style>
.badge { @apply bg-[#1f2937] px-2 py-1 rounded text-gray-300; }
.genre-tag { @apply bg-indigo-500/10 text-indigo-400 px-3 py-1 rounded-full text-sm hover:bg-indigo-600 hover:text-white transition; }
.section-title { @apply text-xl font-semibold text-white mb-4; }
.chapter-card { @apply flex justify-between items-center p-3 bg-[#111827] border border-gray-800 rounded-lg hover:bg-[#1f2937] transition; }

.anime-card { @apply relative rounded-xl overflow-hidden bg-[#111827] aspect-[2/3]; }
.anime-img { @apply w-full h-full object-cover group-hover:scale-105 transition duration-300; }
.anime-title { @apply text-sm text-gray-300 mt-2 truncate group-hover:text-white; }
</style>
@endsection