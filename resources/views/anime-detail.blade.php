@extends('layouts.main')

@section('title', $anime->title)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <div class="relative rounded-xl overflow-hidden h-[300px] md:h-[400px] mb-8">
        <img src="{{ $anime->banner_url }}" class="w-full h-full object-cover" alt="">
        <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/50 to-transparent"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-1">
            <img src="{{ $anime->thumbnail_url }}" class="w-full rounded-lg shadow-lg" alt="">
            <a href="{{ route('watch', $anime->slug) }}" class="block w-full bg-purple-600 hover:bg-purple-700 text-white text-center py-3 rounded-lg font-semibold mt-4 transition">
                Watch Now
            </a>
            @auth
            <button onclick="toggleFavorite({{ $anime->id }})" class="block w-full bg-gray-800 hover:bg-gray-700 text-white text-center py-3 rounded-lg font-semibold mt-2 transition">
                {{ $isFavorited ? 'Remove from Favorites' : 'Add to Favorites' }}
            </button>
            @endauth
        </div>

        <div class="lg:col-span-3">
            <h1 class="text-3xl font-bold mb-2">{{ $anime->title }}</h1>
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-400 mb-4">
                @if($anime->rating)<span class="flex items-center"><svg class="w-4 h-4 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>{{ $anime->rating }}</span>@endif
                @if($anime->score)<span>Score: {{ $anime->score }}</span>@endif
                @if($anime->type)<span class="bg-gray-800 px-2 py-1 rounded">{{ $anime->type }}</span>@endif
                @if($anime->status)<span class="bg-gray-800 px-2 py-1 rounded">{{ $anime->status }}</span>@endif
                @if($anime->year)<span>{{ $anime->year }}</span>@endif
                @if($anime->season)<span>{{ $anime->season }}</span>@endif
                @if($anime->duration)<span>{{ $anime->duration }} min/ep</span>@endif
                @if($anime->episodes_count)<span>{{ $anime->episodes_count }} eps</span>@endif
            </div>

            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($anime->genres as $genre)
                <a href="{{ route('genre', $genre->slug) }}" class="bg-purple-600/20 text-purple-400 px-3 py-1 rounded-full text-sm hover:bg-purple-600 hover:text-white transition">{{ $genre->name }}</a>
                @endforeach
            </div>

            <div class="text-gray-300 mb-6">{{ $anime->description ?? 'No description available.' }}</div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm mb-8">
                @if($anime->studio)<div><span class="text-gray-500">Studio:</span> {{ $anime->studio }}</div>@endif
                @if($anime->source)<div><span class="text-gray-500">Source:</span> {{ $anime->source }}</div>@endif
                @if($anime->country)<div><span class="text-gray-500">Country:</span> {{ $anime->country }}</div>@endif
                @if($anime->producers)<div><span class="text-gray-500">Producers:</span> {{ $anime->producers }}</div>@endif
                @if($anime->licensors)<div><span class="text-gray-500">Licensors:</span> {{ $anime->licensors }}</div>@endif
                @if($anime->views)<div><span class="text-gray-500">Views:</span> {{ number_format($anime->views) }}</div>@endif
            </div>

            @if($anime->episodes->count())
            <h2 class="text-xl font-bold mb-4">Episodes</h2>
            <div class="space-y-2">
                @foreach($anime->episodes as $ep)
                <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $ep->number]) }}" class="flex items-center justify-between p-3 bg-gray-900 rounded-lg hover:bg-gray-800 transition">
                    <div class="flex items-center space-x-3">
                        <span class="text-purple-500 font-bold">Ep {{ $ep->number }}</span>
                        <span>{{ $ep->title ?? 'Episode' }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($ep->has_sub)<span class="text-xs bg-blue-600 px-2 py-1 rounded">SUB</span>@endif
                        @if($ep->has_dub)<span class="text-xs bg-green-600 px-2 py-1 rounded">DUB</span>@endif
                    </div>
                </a>
                @endforeach
            </div>
            @endif

            @if($related->count())
            <h2 class="text-xl font-bold mt-8 mb-4">Related Anime</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach($related as $rel)
                <a href="{{ route('anime.detail', $rel->slug) }}" class="group">
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
function toggleFavorite(animeId) {
    fetch('/favorites/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ anime_id: animeId })
    }).then(r => r.json()).then(d => { if(d.status) location.reload(); });
}
</script>
@endsection
