@extends('admin.layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    <a href="{{ route('admin.jikan.search') }}" class="text-purple-500 hover:text-purple-400 text-sm mb-4 inline-block">&larr; Back to search</a>

    <div class="bg-gray-900 rounded-lg overflow-hidden mb-6">
        <div class="flex flex-col md:flex-row">
            @if($anime['thumbnail'])
            <div class="md:w-64 flex-shrink-0">
                <img src="{{ $anime['thumbnail'] }}" alt="{{ $anime['title'] }}" class="w-full h-auto">
            </div>
            @endif
            <div class="p-6 flex-1">
                <h1 class="text-2xl font-bold mb-2">{{ $anime['title'] }}</h1>
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($anime['genres'] as $genre)
                    <span class="bg-purple-600/20 text-purple-400 text-xs px-2 py-1 rounded">{{ $genre['name'] }}</span>
                    @endforeach
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
                    <div>
                        <span class="text-gray-400 block">Type</span>
                        <span>{{ $anime['type'] ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Status</span>
                        <span>{{ $anime['status'] ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Episodes</span>
                        <span>{{ $anime['episodes_count'] ?: '?' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Score</span>
                        <span>{{ $anime['score'] ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Season</span>
                        <span>{{ $anime['season'] ? "{$anime['season']} {$anime['year']}" : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Studio</span>
                        <span>{{ $anime['studio'] ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Duration</span>
                        <span>{{ $anime['duration'] ? "{$anime['duration']} min" : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Source</span>
                        <span>{{ $anime['source'] ?: '-' }}</span>
                    </div>
                </div>
                @if($anime['description'])
                <p class="text-gray-300 text-sm leading-relaxed">{{ $anime['description'] }}</p>
                @endif
            </div>
        </div>
    </div>

    @if(count($episodes) > 0)
    <div class="bg-gray-900 rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-800">
            <h2 class="text-lg font-semibold">Episodes ({{ count($episodes) }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-800">
                        <th class="text-left p-3">#</th>
                        <th class="text-left p-3">Title</th>
                        <th class="text-left p-3">Aired</th>
                        <th class="text-left p-3">Duration</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($episodes as $ep)
                    <tr class="border-b border-gray-800 hover:bg-gray-800/50">
                        <td class="p-3">{{ $ep['number'] }}</td>
                        <td class="p-3">{{ $ep['title'] ?: 'Episode ' . $ep['number'] }}</td>
                        <td class="p-3 text-gray-400">{{ $ep['air_date'] ?: '-' }}</td>
                        <td class="p-3 text-gray-400">{{ $ep['duration'] ? "{$ep['duration']} min" : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="flex items-center justify-between">
        @if($alreadyImported)
        <div class="bg-yellow-600/20 text-yellow-400 px-4 py-3 rounded-lg text-sm">
            This anime has already been imported.
        </div>
        @else
        <form action="{{ route('admin.jikan.import', $anime['mal_id']) }}" method="POST">
            @csrf
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg font-medium"
                onclick="return confirm('Import {{ $anime['title'] }} with {{ count($episodes) }} episodes?')">
                Import "{{ $anime['title'] }}"
            </button>
        </form>
        @endif
        <span class="text-sm text-gray-500">MAL ID: {{ $anime['mal_id'] }}</span>
    </div>
</div>
@endsection
