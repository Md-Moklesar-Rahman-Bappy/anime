@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">MAL Import</h1>

    @if(session('success'))
    <div class="bg-green-600 text-white px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-600 text-white px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
    @endif

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
            <div class="space-y-2">
                <form action="{{ route('admin.jikan.batch-import') }}" method="POST">
                    @csrf
                    <input type="hidden" name="batch_size" value="10">
                    <input type="hidden" name="with_episodes" value="0">
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm"
                        onclick="return confirm('Import the next 10 anime (metadata only)?')">
                        Import Next 10
                    </button>
                </form>
                <form action="{{ route('admin.jikan.batch-import') }}" method="POST">
                    @csrf
                    <input type="hidden" name="batch_size" value="10">
                    <input type="hidden" name="with_episodes" value="1">
                    <button type="submit" class="w-full bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded-lg text-sm"
                        onclick="return confirm('Import next 10 anime WITH episodes (slower)?')">
                        Import Next 10 + Episodes
                    </button>
                </form>
                <form action="{{ route('admin.jikan.batch-import') }}" method="POST">
                    @csrf
                    <input type="hidden" name="batch_size" value="50">
                    <input type="hidden" name="with_episodes" value="0">
                    <button type="submit" class="w-full bg-purple-800 hover:bg-purple-900 text-white px-4 py-2 rounded-lg text-sm"
                        onclick="return confirm('Import the next 50 anime? This may take a minute.')">
                        Import Next 50
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
            <div class="bg-gray-900 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-gray-400 border-b border-gray-800">
                            <th class="text-left p-3 w-16"></th>
                            <th class="text-left p-3">Title</th>
                            <th class="text-left p-3">Type</th>
                            <th class="text-left p-3">Episodes</th>
                            <th class="text-left p-3">Score</th>
                            <th class="text-left p-3">Status</th>
                            <th class="text-left p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $item)
                        <tr class="border-b border-gray-800 hover:bg-gray-800/50">
                            <td class="p-3">
                                @if($item['thumbnail'])
                                <img src="{{ $item['thumbnail'] }}" alt="" class="w-12 h-16 object-cover rounded">
                                @endif
                            </td>
                            <td class="p-3 font-medium">{{ $item['title'] }}</td>
                            <td class="p-3">{{ $item['type'] }}</td>
                            <td class="p-3">{{ $item['episodes_count'] ?: '?' }}</td>
                            <td class="p-3">{{ $item['score'] ?: '-' }}</td>
                            <td class="p-3">{{ $item['status'] }}</td>
                            <td class="p-3">
                                @if(in_array($item['mal_id'], $existingMalIds ?? []))
                                    <span class="text-gray-500 text-xs">Imported</span>
                                @else
                                    <a href="{{ route('admin.jikan.preview', $item['mal_id']) }}" class="text-purple-500 hover:text-purple-400 text-sm">
                                        Preview
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
