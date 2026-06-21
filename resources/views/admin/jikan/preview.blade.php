@extends('admin.layouts.app')

@section('content')

<div class="max-w-6xl mx-auto">

    {{-- BACK --}}
    <a href="{{ route('admin.jikan.search') }}"
       class="text-indigo-400 hover:text-indigo-300 text-sm mb-4 inline-block">
        ← Back to search
    </a>


    {{-- ANIME CARD --}}
    <div class="table-card mb-6 overflow-hidden">

        <div class="flex flex-col md:flex-row">

            {{-- IMAGE --}}
            @if($anime['thumbnail'])
            <div class="md:w-1/4">
                <img src="{{ $anime['thumbnail'] }}"
                     class="w-full h-full object-cover">
            </div>
            @endif

            {{-- CONTENT --}}
            <div class="p-6 flex-1">

                <h1 class="text-xl font-semibold text-white mb-3">
                    {{ $anime['title'] }}
                </h1>

                {{-- GENRES --}}
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($anime['genres'] as $genre)
                        <span class="px-2 py-1 text-xs bg-indigo-500/10 text-indigo-400 rounded">
                            {{ $genre['name'] }}
                        </span>
                    @endforeach
                </div>

                {{-- DETAILS --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">

                    <div>
                        <p class="text-gray-500">Type</p>
                        <p class="text-gray-300">{{ $anime['type'] ?: '-' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Status</p>
                        <p class="text-gray-300">{{ $anime['status'] ?: '-' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Episodes</p>
                        <p class="text-gray-300">{{ $anime['episodes_count'] ?: '?' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Score</p>
                        <p class="text-gray-300">{{ $anime['score'] ?: '-' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Season</p>
                        <p class="text-gray-300">
                            {{ $anime['season'] ? "{$anime['season']} {$anime['year']}" : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Studio</p>
                        <p class="text-gray-300">{{ $anime['studio'] ?: '-' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Duration</p>
                        <p class="text-gray-300">
                            {{ $anime['duration'] ? "{$anime['duration']} min" : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Source</p>
                        <p class="text-gray-300">{{ $anime['source'] ?: '-' }}</p>
                    </div>

                </div>

                {{-- DESCRIPTION --}}
                @if($anime['description'])
                <p class="text-gray-400 text-sm leading-relaxed">
                    {{ $anime['description'] }}
                </p>
                @endif

            </div>

        </div>
    </div>


    {{-- EPISODES --}}
    @if(count($episodes) > 0)
    <div class="table-card mb-6 overflow-hidden">

        <div class="p-4 border-b border-gray-700">
            <h2 class="text-lg text-white">
                Episodes ({{ count($episodes) }})
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="table-head">
                    <tr>
                        <th class="p-4 text-left">#</th>
                        <th class="p-4 text-left">Title</th>
                        <th class="p-4 text-left">Aired</th>
                        <th class="p-4 text-left">Duration</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($episodes as $ep)
                    <tr class="table-row">

                        <td class="p-4 text-white">
                            {{ $ep['number'] }}
                        </td>

                        <td class="p-4 text-gray-300">
                            {{ $ep['title'] ?: 'Episode '.$ep['number'] }}
                        </td>

                        <td class="p-4 text-gray-400">
                            {{ $ep['air_date'] ?: '-' }}
                        </td>

                        <td class="p-4 text-gray-400">
                            {{ $ep['duration'] ? "{$ep['duration']} min" : '-' }}
                        </td>

                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
    @endif


    {{-- IMPORT ACTION --}}
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">

        <form action="{{ route('admin.jikan.import', $anime['mal_id']) }}" method="POST">
            @csrf

            <button type="submit"
                onclick="return confirm('{{ $alreadyImported ? 'Re-import (update)' : 'Import' }} {{ $anime['title'] }}?')"
                class="btn-primary px-6 py-3">

                {{ $alreadyImported ? 'Re-import (Update)' : 'Import Anime' }}

            </button>
        </form>

        <div class="text-xs text-gray-500">
            MAL ID: {{ $anime['mal_id'] }} • {{ count($episodes) }} episodes
        </div>

    </div>

</div>

@endsection