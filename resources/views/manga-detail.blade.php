@extends('layouts.main')

@section('title', $manga->title)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <div class="relative rounded-xl overflow-hidden h-[300px] md:h-[400px] mb-8">
        <img src="{{ $manga->banner_url }}" class="w-full h-full object-cover" alt="">
        <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/50 to-transparent"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-1">
            <img src="{{ $manga->thumbnail_url }}" class="w-full rounded-lg shadow-lg" alt="" loading="lazy">
            <a href="{{ route('manga.read', $manga->slug) }}" class="block w-full bg-purple-600 hover:bg-purple-700 text-white text-center py-3 rounded-lg font-semibold mt-4 transition">
                Read Now
            </a>
            @auth
            <button onclick="toggleMangaFavorite({{ $manga->id }})" class="block w-full bg-gray-800 hover:bg-gray-700 text-white text-center py-3 rounded-lg font-semibold mt-2 transition">
                {{ $isFavorited ? 'Remove from Favorites' : 'Add to Favorites' }}
            </button>
            @endauth
        </div>

        <div class="lg:col-span-3">
            <h1 class="text-3xl font-bold mb-2">{{ $manga->title }}</h1>
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-400 mb-4">
                @if($manga->rating)<span class="flex items-center"><svg class="w-4 h-4 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>{{ $manga->rating }}</span>@endif
                @if($manga->score)<span>Score: {{ $manga->score }}</span>@endif
                @if($manga->type)<span class="bg-gray-800 px-2 py-1 rounded">{{ $manga->type }}</span>@endif
                @if($manga->status)<span class="bg-gray-800 px-2 py-1 rounded">{{ $manga->status }}</span>@endif
                @if($manga->year)<span>{{ $manga->year }}</span>@endif
                @if($manga->chapters_count)<span>{{ $manga->chapters_count }} ch.</span>@endif
            </div>

            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($manga->genres as $genre)
                <a href="{{ route('manga.genre', $genre->slug) }}" class="bg-purple-600/20 text-purple-400 px-3 py-1 rounded-full text-sm hover:bg-purple-600 hover:text-white transition">{{ $genre->name }}</a>
                @endforeach
            </div>

            <div class="text-gray-300 mb-6">{{ $manga->description ?? 'No description available.' }}</div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm mb-8">
                @if($manga->author)<div><span class="text-gray-500">Author:</span> {{ $manga->author }}</div>@endif
                @if($manga->artist)<div><span class="text-gray-500">Artist:</span> {{ $manga->artist }}</div>@endif
                @if($manga->publisher)<div><span class="text-gray-500">Publisher:</span> {{ $manga->publisher }}</div>@endif
                @if($manga->source)<div><span class="text-gray-500">Source:</span> {{ $manga->source }}</div>@endif
                @if($manga->alternative_titles)<div><span class="text-gray-500">Alternative:</span> {{ $manga->alternative_titles }}</div>@endif
                @if($manga->views)<div><span class="text-gray-500">Views:</span> {{ number_format($manga->views) }}</div>@endif
            </div>

            @if($manga->chapters->count())
            <h2 class="text-xl font-bold mb-4">Chapters</h2>
            <div class="space-y-2">
                @foreach($manga->chapters as $ch)
                <a href="{{ route('manga.read', ['slug' => $manga->slug, 'chapter' => $ch->number]) }}" class="flex items-center justify-between p-3 bg-gray-900 rounded-lg hover:bg-gray-800 transition">
                    <div>
                        <span class="text-purple-500 font-bold">Ch. {{ rtrim(rtrim($ch->number, '0'), '.') }}</span>
                        @if($ch->title)<span class="ml-2 text-gray-400">{{ $ch->title }}</span>@endif
                    </div>
                    <span class="text-xs text-gray-500">{{ $ch->pages_count }} pages</span>
                </a>
                @endforeach
            </div>
            @endif

            @if($related->count())
            <h2 class="text-xl font-bold mt-8 mb-4">Related Manga</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach($related as $rel)
                <a href="{{ route('manga.detail', $rel->slug) }}" class="group">
                    <div class="relative rounded-lg overflow-hidden bg-gray-800 aspect-[2/3]">
                        <img src="{{ $rel->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="">
                    </div>
                    <h3 class="text-sm text-gray-300 mt-2 line-clamp-1 group-hover:text-white">{{ $rel->title }}</h3>
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleMangaFavorite(mangaId) {
    fetch('/manga/favorites/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ manga_id: mangaId })
    }).then(r => r.json()).then(d => { if(d.status) location.reload(); });
}
</script>
@endsection
