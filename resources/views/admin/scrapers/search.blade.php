@extends('admin.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Import from External Sources</h1>

    @if(isset($anime))
    <div class="bg-gray-800 rounded-lg p-3 mb-4 flex items-center justify-between">
        <p class="text-sm">Importing episodes for: <span class="font-semibold text-white">{{ $anime->title }}</span></p>
        <a href="{{ route('admin.anime.episodes.index', $anime) }}" class="text-purple-500 hover:text-purple-400 text-sm">Back to episodes</a>
    </div>
    @endif

    <div class="bg-gray-900 rounded-lg p-6 mb-6">
        <form action="{{ route('admin.scrapers.search.results') }}" method="POST" class="flex items-end space-x-4">
            @csrf
            <div class="flex-1">
                <label class="block text-sm text-gray-400 mb-1">Source</label>
                <select name="scraper" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700" required>
                    @foreach($scrapers as $s)
                        <option value="{{ $s['class'] }}" @selected(isset($scraper) && get_class($scraper) === $s['class'])>
                            {{ $s['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-[2]">
                <label class="block text-sm text-gray-400 mb-1">Search Anime</label>
                <input type="text" name="query" value="{{ request('query') }}" placeholder="Search for anime..." class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700" required>
            </div>
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">Search</button>
        </form>
    </div>

    @if(isset($results))
        <h2 class="text-xl font-bold mb-4">Results from {{ $scraper->name() }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @forelse($results as $result)
                <div class="bg-gray-900 rounded-lg p-4">
                    @if($result['image'] ?? false)
                        <img src="{{ $result['image'] }}" alt="" class="w-full h-48 object-cover rounded-lg mb-3">
                    @endif
                    <h3 class="font-semibold text-sm mb-2">{{ $result['title'] }}</h3>
                    @if($result['released'] ?? false)
                        <p class="text-xs text-gray-500 mb-3">{{ $result['released'] }}</p>
                    @endif
                    <form action="{{ route('admin.scrapers.preview') }}" method="POST">
                        @csrf
                        <input type="hidden" name="scraper" value="{{ get_class($scraper) }}">
                        <input type="hidden" name="anime_id" value="{{ $result['id'] }}">
                        <input type="hidden" name="anime_title" value="{{ $result['title'] }}">
                        <input type="hidden" name="anime_image" value="{{ $result['image'] ?? '' }}">
                        @if(isset($anime))
                            <input type="hidden" name="local_anime_id" value="{{ $anime->id }}">
                        @endif
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white text-sm px-3 py-2 rounded-lg">View Episodes</button>
                    </form>
                </div>
            @empty
                <p class="col-span-3 text-gray-500">No results found.</p>
            @endforelse
        </div>
    @endif
</div>
@endsection
