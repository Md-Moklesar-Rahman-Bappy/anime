@extends('admin.layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">

    <!-- Back -->
    <a href="{{ route('admin.jikan.search') }}"
       class="text-indigo-400 hover:text-indigo-300 text-sm mb-4 inline-block transition">
        ← Back to search
    </a>

    <!-- Anime Card -->
    <div class="bg-[#111827] border border-gray-800 rounded-2xl overflow-hidden mb-6 shadow">

        <div class="flex flex-col md:flex-row">

            @if($anime['thumbnail'])
            <div class="md:w-64 flex-shrink-0">
                <img src="{{ $anime['thumbnail'] }}"
                     class="w-full h-full object-cover">
            </div>
            @endif

            <div class="p-6 flex-1">

                <h1 class="text-2xl font-semibold text-white mb-2">
                    {{ $anime['title'] }}
                </h1>

                <!-- Genres -->
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($anime['genres'] as $genre)
                        <span class="bg-indigo-500/10 text-indigo-400 text-xs px-2 py-1 rounded-lg">
                            {{ $genre['name'] }}
                        </span>
                    @endforeach
                </div>

                <!-- Info -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">

                    <div><span class="text-gray-500 block">Type</span><span class="text-gray-300">{{ $anime['type'] ?: '-' }}</span></div>
                    <div><span class="text-gray-500 block">Status</span><span class="text-gray-300">{{ $anime['status'] ?: '-' }}</span></div>
                    <div><span class="text-gray-500 block">Episodes</span><span class="text-gray-300">{{ $anime['episodes_count'] ?: '?' }}</span></div>
                    <div><span class="text-gray-500 block">Score</span><span class="text-gray-300">{{ $anime['score'] ?: '-' }}</span></div>
                    <div><span class="text-gray-500 block">Season</span><span class="text-gray-300">{{ $anime['season'] ? "{$anime['season']} {$anime['year']}" : '-' }}</span></div>
                    <div><span class="text-gray-500 block">Studio</span><span class="text-gray-300">{{ $anime['studio'] ?: '-' }}</span></div>
                    <div><span class="text-gray-500 block">Duration</span><span class="text-gray-300">{{ $anime['duration'] ? "{$anime['duration']} min" : '-' }}</span></div>
                    <div><span class="text-gray-500 block">Source</span><span class="text-gray-300">{{ $anime['source'] ?: '-' }}</span></div>

                </div>

                @if($anime['description'])
                <p class="text-gray-400 text-sm leading-relaxed">
                    {{ $anime['description'] }}
                </p>
                @endif

            </div>
        </div>
    </div>

    <!-- Episodes -->
    @if(count($episodes) > 0)
    <div class="bg-[#111827] border border-gray-800 rounded-2xl overflow-hidden mb-6">

        <div class="px-6 py-4 border-b border-gray-800">
            <h2 class="text-lg font-medium text-white">
                Episodes ({{ count($episodes) }})
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead>
                    <tr class="bg-[#0f172a] text-gray-400 border-b border-gray-800">
                        <th class="p-3 text-left">#</th>
                        <th class="p-3 text-left">Title</th>
                        <th class="p-3 text-left">Aired</th>
                        <th class="p-3 text-left">Duration</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($episodes as $ep)
                    <tr class="border-b border-gray-800 hover:bg-[#1f2937] transition">
                        <td class="p-3 text-white">{{ $ep['number'] }}</td>
                        <td class="p-3 text-gray-300">{{ $ep['title'] ?: 'Episode '.$ep['number'] }}</td>
                        <td class="p-3 text-gray-400">{{ $ep['air_date'] ?: '-' }}</td>
                        <td class="p-3 text-gray-400">{{ $ep['duration'] ? "{$ep['duration']} min" : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
    @endif

    <!-- Import -->
    <div class="flex items-center justify-between gap-4">

        <form action="{{ route('admin.jikan.import', $anime['mal_id']) }}" method="POST">
            @csrf

            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-lg font-medium transition"
                onclick="return confirm('{{ $alreadyImported ? 'Re-import (update)' : 'Import' }} {{ $anime['title'] }}?')">

                {{ $alreadyImported ? 'Re-import (Update)' : 'Import Anime' }}
            </button>
        </form>

        <span class="text-sm text-gray-500">
            MAL ID: {{ $anime['mal_id'] }} • {{ count($episodes) }} episodes
        </span>

    </div>

</div>
@endsection