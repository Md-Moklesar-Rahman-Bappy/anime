@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">MAL Import</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-gray-900 rounded-lg p-6 col-span-2">
            <h2 class="text-lg font-semibold mb-4">Search & Import</h2>
            <form action="{{ route('admin.jikan.search.results') }}" method="POST">
                @csrf
                <div class="flex gap-4">
                    <input type="text" name="q" value="{{ old('q', $query ?? '') }}" placeholder="Search MyAnimeList..." class="flex-1 bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500" required>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">Search</button>
                </div>
            </form>

            <div class="mt-4 flex gap-2 text-sm text-gray-400">
                <span>Or try:</span>
                <form action="{{ route('admin.jikan.search.results') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="q" value="One Piece">
                    <button class="text-purple-400 hover:text-purple-300 underline">One Piece</button>
                </form>
                <form action="{{ route('admin.jikan.search.results') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="q" value="Naruto">
                    <button class="text-purple-400 hover:text-purple-300 underline">Naruto</button>
                </form>
                <form action="{{ route('admin.jikan.search.results') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="q" value="Attack on Titan">
                    <button class="text-purple-400 hover:text-purple-300 underline">Attack on Titan</button>
                </form>
                <form action="{{ route('admin.jikan.search.results') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="q" value="Demon Slayer">
                    <button class="text-purple-400 hover:text-purple-300 underline">Demon Slayer</button>
                </form>
            </div>
        </div>

        <div class="bg-gray-900 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Mass Import</h2>
            <p class="text-sm text-gray-400 mb-3">
                Imported: <span class="text-white font-medium">{{ $totalImported ?? 0 }}</span> anime
                @if(isset($lastMalId) && $lastMalId)
                    <br>Progress: <span class="text-purple-400">MAL #{{ $lastMalId }}</span>
                @endif
            </p>
            <p class="text-xs text-gray-500 mb-3">Each import includes anime metadata + all episodes.</p>
            <div class="space-y-2">
                <form action="{{ route('admin.jikan.batch-import') }}" method="POST">
                    @csrf
                    <input type="hidden" name="batch_size" value="5">
                    <input type="hidden" name="with_episodes" value="1">
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm"
                        onclick="return confirm('Import the next 5 anime with all episodes?')">
                        Import Next 5 + All Episodes
                    </button>
                </form>
                <form action="{{ route('admin.jikan.batch-import') }}" method="POST">
                    @csrf
                    <input type="hidden" name="batch_size" value="10">
                    <input type="hidden" name="with_episodes" value="1">
                    <button type="submit" class="w-full bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded-lg text-sm"
                        onclick="return confirm('Import the next 10 anime with all episodes?')">
                        Import Next 10 + All Episodes
                    </button>
                </form>
                <form action="{{ route('admin.jikan.batch-import') }}" method="POST">
                    @csrf
                    <input type="hidden" name="batch_size" value="25">
                    <input type="hidden" name="with_episodes" value="1">
                    <button type="submit" class="w-full bg-purple-800 hover:bg-purple-900 text-white px-4 py-2 rounded-lg text-sm"
                        onclick="return confirm('Import the next 25 anime with all episodes? This may take a while.')">
                        Import Next 25 + All Episodes
                    </button>
                </form>
                @if(isset($lastMalId) && $lastMalId)
                <form action="{{ route('admin.jikan.reset-progress') }}" method="POST" class="pt-2 border-t border-gray-800">
                    @csrf
                    <button type="submit" class="w-full bg-gray-800 hover:bg-gray-700 text-gray-400 px-4 py-2 rounded-lg text-sm"
                        onclick="return confirm('Reset import progress? You will restart from the beginning.')">
                        Reset Progress
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    @isset($results)
        @if(count($results) > 0)
            <div class="space-y-2">
                @foreach($results as $item)
                <div class="bg-gray-900 rounded-lg overflow-hidden hover:bg-gray-800/80 transition-colors">
                    <div class="flex">
                        <div class="w-20 flex-shrink-0">
                            @if($item['thumbnail'])
                            <img src="{{ $item['thumbnail'] }}" alt="" class="w-full h-28 object-cover">
                            @endif
                        </div>
                        <div class="flex-1 p-3 flex flex-col justify-between min-w-0">
                            <div>
                                <h3 class="font-semibold truncate">{{ $item['title'] }}</h3>
                                <div class="flex flex-wrap gap-1.5 mt-1">
                                    <span class="text-xs bg-gray-800 text-gray-400 px-2 py-0.5 rounded">{{ $item['type'] ?: 'N/A' }}</span>
                                    <span class="text-xs bg-gray-800 text-gray-400 px-2 py-0.5 rounded">{{ $item['episodes_count'] ? $item['episodes_count'].' eps' : '? eps' }}</span>
                                    @if($item['score'])
                                    <span class="text-xs bg-yellow-900/30 text-yellow-400 px-2 py-0.5 rounded">{{ $item['score'] }}</span>
                                    @endif
                                    <span class="text-xs bg-gray-800 text-gray-400 px-2 py-0.5 rounded">{{ $item['status'] }}</span>
                                </div>
                            </div>
                            <div class="flex gap-2 items-center mt-2">
                                @if(in_array($item['mal_id'], $existingMalIds ?? []))
                                    <span class="text-xs text-gray-500">Imported</span>
                                @else
                                    <a href="{{ route('admin.jikan.preview', $item['mal_id']) }}" class="text-xs text-purple-400 hover:text-purple-300 font-medium">
                                        Preview & Import
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if(isset($pagination))
            <div class="mt-4 flex items-center justify-between">
                <span class="text-sm text-gray-400">
                    Page {{ $currentPage }} of {{ $pagination['last_visible_page'] ?? 1 }}
                </span>
                <div class="flex gap-2">
                    @if(($pagination['current_page'] ?? 1) > 1)
                    <form action="{{ route('admin.jikan.search.results') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="q" value="{{ $query }}">
                        <input type="hidden" name="page" value="{{ ($pagination['current_page'] ?? 1) - 1 }}">
                        <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm">Previous</button>
                    </form>
                    @endif
                    @if($pagination['has_next_page'] ?? false)
                    <form action="{{ route('admin.jikan.search.results') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="q" value="{{ $query }}">
                        <input type="hidden" name="page" value="{{ ($pagination['current_page'] ?? 1) + 1 }}">
                        <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm">Next</button>
                    </form>
                    @endif
                </div>
            </div>
            @endif
        @else
            <p class="text-gray-400">No results found for "{{ $query }}".</p>
        @endif
    @else
        <div class="bg-gray-900 rounded-lg p-8 text-center text-gray-500">
            <p class="text-lg mb-2">Search for anime on MyAnimeList</p>
            <p class="text-sm">Enter a title above to find and import anime from MAL via the Jikan API, or use the Mass Import panel to bulk-import.</p>
        </div>
    @endisset
</div>
@endsection
