@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">

    <h1 class="text-2xl font-semibold text-white mb-6">MAL Import</h1>

    <!-- Top Panels -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        <!-- Search -->
        <div class="bg-[#111827] border border-gray-800 rounded-2xl p-6 col-span-2">

            <h2 class="text-lg font-medium text-white mb-4">Search & Import</h2>

            <form action="{{ route('admin.jikan.search.results') }}" method="POST">
                @csrf
                <div class="flex gap-4">
                    <input type="text" name="q"
                        value="{{ old('q', $query ?? '') }}"
                        placeholder="Search MyAnimeList..."
                        class="form-input flex-1"
                        required>

                    <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-lg transition">
                        Search
                    </button>
                </div>
            </form>

            <!-- Suggestions -->
            <div class="mt-4 flex flex-wrap gap-2 text-sm text-gray-400">
                <span>Try:</span>

                @foreach(['One Piece','Naruto','Attack on Titan','Demon Slayer'] as $s)
                <form action="{{ route('admin.jikan.search.results') }}" method="POST">
                    @csrf
                    <input type="hidden" name="q" value="{{ $s }}">
                    <button class="text-indigo-400 hover:text-indigo-300 underline">
                        {{ $s }}
                    </button>
                </form>
                @endforeach
            </div>

        </div>

        <!-- Mass Import -->
        <div class="bg-[#111827] border border-gray-800 rounded-2xl p-6">

            <h2 class="text-lg font-medium text-white mb-3">Mass Import</h2>

            <p class="text-sm text-gray-400 mb-3">
                Imported: <span class="text-white">{{ $totalImported ?? 0 }}</span>
                @if(!empty($lastMalId))
                    <br>Progress: <span class="text-indigo-400">#{{ $lastMalId }}</span>
                @endif
            </p>

            <div class="space-y-2">

                @foreach([5,10,25] as $batch)
                <form action="{{ route('admin.jikan.batch-import') }}" method="POST">
                    @csrf
                    <input type="hidden" name="batch_size" value="{{ $batch }}">
                    <input type="hidden" name="with_episodes" value="1">

                    <button class="w-full bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm transition">
                        Import Next {{ $batch }}
                    </button>
                </form>
                @endforeach

                @if(!empty($lastMalId))
                <form action="{{ route('admin.jikan.reset-progress') }}" method="POST">
                    @csrf
                    <button class="w-full bg-gray-800 hover:bg-gray-700 text-gray-400 px-4 py-2 rounded-lg text-sm">
                        Reset Progress
                    </button>
                </form>
                @endif

            </div>

        </div>
    </div>

    <!-- Results -->
    @isset($results)

        @if(count($results) > 0)

        <div class="space-y-2">

            @foreach($results as $item)
            <div class="bg-[#111827] border border-gray-800 rounded-2xl hover:bg-[#1f2937] transition">

                <div class="flex">

                    <!-- Image -->
                    <div class="w-20">
                        @if($item['thumbnail'])
                            <img src="{{ $item['thumbnail'] }}" class="w-full h-28 object-cover">
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 p-3">

                        <h3 class="text-white font-medium truncate">
                            {{ $item['title'] }}
                        </h3>

                        <div class="flex flex-wrap gap-2 mt-1 text-xs text-gray-400">

                            <span class="bg-gray-800 px-2 py-1 rounded">
                                {{ $item['type'] ?? 'N/A' }}
                            </span>

                            <span class="bg-gray-800 px-2 py-1 rounded">
                                {{ $item['episodes_count'] ? $item['episodes_count'].' eps' : '?' }}
                            </span>

                            @if($item['score'])
                            <span class="bg-yellow-500/10 text-yellow-400 px-2 py-1 rounded">
                                {{ $item['score'] }}
                            </span>
                            @endif

                        </div>

                        <div class="mt-2">

                        @if(in_array($item['mal_id'], $existingMalIds ?? []))
                            <span class="text-gray-500 text-xs">Imported</span>
                        @else
                            <a href="{{ route('admin.jikan.preview', $item['mal_id']) }}"
                               class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">
                                Preview & Import
                            </a>
                        @endif

                        </div>
                    </div>

                </div>

            </div>
            @endforeach

        </div>

        @else
            <p class="text-gray-400">No results found.</p>
        @endif

    @else
        <div class="bg-[#111827] border border-gray-800 p-8 rounded-2xl text-center text-gray-500">
            <p class="text-lg text-gray-300 mb-2">Search for anime</p>
            <p class="text-sm">Use the form above to import data from MAL.</p>
        </div>
    @endisset

</div>

<style>
.form-input {
    @apply w-full px-3 py-2 bg-[#1f2937] border border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500;
}
</style>

@endsection
