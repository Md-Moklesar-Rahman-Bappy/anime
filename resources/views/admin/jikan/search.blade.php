@extends('admin.layouts.app')

@section('content')

<div class="max-w-6xl mx-auto">

    {{-- TITLE --}}
    <h1 class="text-xl font-semibold text-white mb-6">
        MAL Import
    </h1>

    {{-- TOP SECTION --}}
    <div class="grid lg:grid-cols-3 gap-6 mb-6">

        {{-- SEARCH --}}
        <div class="lg:col-span-2 table-card p-5">

            <h2 class="text-lg font-medium text-white mb-4">
                Search & Import
            </h2>

            <form action="{{ route('admin.jikan.search.results') }}" method="POST">
                @csrf

                <div class="flex gap-3">

                    <input type="text"
                           name="q"
                           value="{{ old('q', $query ?? '') }}"
                           placeholder="Search MyAnimeList..."
                           required
                           class="flex-1 form-input">

                    <button class="btn-primary">
                        Search
                    </button>

                </div>

            </form>

            {{-- QUICK SEARCH --}}
            <div class="mt-4 flex flex-wrap gap-2 text-sm text-gray-400">

                <span>Try:</span>

                @foreach(['One Piece','Naruto','Attack on Titan','Demon Slayer'] as $s)
                    <form action="{{ route('admin.jikan.search.results') }}" method="POST">
                        @csrf
                        <input type="hidden" name="q" value="{{ $s }}">
                        <button class="text-indigo-400 hover:underline">
                            {{ $s }}
                        </button>
                    </form>
                @endforeach

            </div>

        </div>


        {{-- MASS IMPORT --}}
        <div class="table-card p-5">

            <h2 class="text-lg font-medium text-white mb-2">
                Mass Import
            </h2>

            <p class="text-sm text-gray-400 mb-3">
                Imported:
                <span class="text-white">{{ $totalImported ?? 0 }}</span>

                @if(!empty($lastMalId))
                    <br>
                    Progress:
                    <span class="text-indigo-400">#{{ $lastMalId }}</span>
                @endif
            </p>

            <div class="flex flex-col gap-2">

                @foreach([5,10,25] as $batch)
                    <form action="{{ route('admin.jikan.batch-import') }}" method="POST">
                        @csrf
                        <input type="hidden" name="batch_size" value="{{ $batch }}">
                        <input type="hidden" name="with_episodes" value="1">

                        <button class="w-full btn-primary text-sm">
                            Import Next {{ $batch }}
                        </button>
                    </form>
                @endforeach

                @if(!empty($lastMalId))
                    <form action="{{ route('admin.jikan.reset-progress') }}" method="POST">
                        @csrf

                        <button class="w-full btn-cancel text-sm">
                            Reset Progress
                        </button>
                    </form>
                @endif

            </div>

        </div>
    </div>


    {{-- RESULTS --}}
    @isset($results)

        @if(count($results) > 0)

        <div class="space-y-3">

            @foreach($results as $item)

            <div class="table-card flex overflow-hidden">

                {{-- IMAGE --}}
                @if($item['thumbnail'])
                    <img src="{{ $item['thumbnail'] }}"
                         class="w-20 h-28 object-cover">
                @endif

                {{-- CONTENT --}}
                <div class="p-4 flex-1">

                    <h3 class="text-white font-medium truncate">
                        {{ $item['title'] }}
                    </h3>

                    {{-- TAGS --}}
                    <div class="flex flex-wrap gap-2 mt-2 text-xs text-gray-400">

                        <span class="bg-gray-800 px-2 py-1 rounded">
                            {{ $item['type'] ?? 'N/A' }}
                        </span>

                        <span class="bg-gray-800 px-2 py-1 rounded">
                            {{ $item['episodes_count'] ? $item['episodes_count'].' eps' : '?' }}
                        </span>

                        @if($item['score'])
                        <span class="bg-yellow-400/10 text-yellow-400 px-2 py-1 rounded">
                            {{ $item['score'] }}
                        </span>
                        @endif

                    </div>

                    {{-- ACTION --}}
                    <div class="mt-3 text-sm">

                        @if(in_array($item['mal_id'], $existingMalIds ?? []))
                            <span class="text-gray-500">
                                Imported
                            </span>
                        @else
                            <a href="{{ route('admin.jikan.preview', $item['mal_id']) }}"
                               class="text-indigo-400 hover:underline">
                                Preview & Import
                            </a>
                        @endif

                    </div>

                </div>

            </div>

            @endforeach

        </div>

        @else

            <p class="text-gray-400">
                No results found.
            </p>

        @endif

    @else

        {{-- EMPTY STATE --}}
        <div class="table-card p-8 text-center text-gray-500">

            <p class="text-white font-medium mb-2">
                Search for anime
            </p>

            <p class="text-sm">
                Use the form above to import data from MAL.
            </p>

        </div>

    @endisset

</div>

@endsection