@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Episodes: {{ $anime->title }}</h1>
            <a href="{{ route('admin.anime.index') }}" class="text-sm text-gray-500 hover:text-white">&larr; Back to Anime</a>
        </div>
        <div class="flex space-x-2">
            @if($anime->mal_id)
            <form action="{{ route('admin.jikan.refresh-episodes', $anime->mal_id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-700 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Refresh from MAL
                </button>
            </form>
            @endif
            <a href="{{ route('admin.anime.episodes.create', $anime) }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm">Add Episode</a>
                <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm border border-gray-700">Quick Import</button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 bg-gray-900 border border-gray-700 rounded-lg shadow-lg z-50">
                    <a href="{{ route('admin.anime.episodes.create', $anime) }}?source=youtube" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-800 rounded-lg">From YouTube URL</a>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-gray-900 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="text-gray-400 border-b border-gray-800"><th class="text-left p-3">#</th><th class="text-left p-3">Title</th><th class="text-left p-3">Source</th><th class="text-left p-3">Duration</th><th class="text-left p-3">Sub</th><th class="text-left p-3">Dub</th><th class="text-left p-3">Actions</th></tr></thead>
            <tbody>
                @foreach($episodes as $ep)
                <tr class="border-b border-gray-800 hover:bg-gray-800/50">
                    <td class="p-3">{{ $ep->number }}</td>
                    <td class="p-3">{{ $ep->title ?? 'Episode '.$ep->number }}</td>
                    <td class="p-3">
                        @switch($ep->source_type)
                            @case('youtube')
                                <span class="text-red-500 text-xs font-semibold">YouTube</span>
                                @break
                            @case('upload')
                                <span class="text-green-500 text-xs font-semibold">Upload</span>
                                @break
                            @case('direct_url')
                                <span class="text-yellow-500 text-xs font-semibold">URL</span>
                                @break
                            @case('external')
                                <span class="text-cyan-500 text-xs font-semibold">External</span>
                                @break
                            @default
                                <span class="text-gray-500 text-xs">{{ $ep->source_type ?? '-' }}</span>
                        @endswitch
                    </td>
                    <td class="p-3">{{ $ep->duration ? $ep->duration.'min' : '-' }}</td>
                    <td class="p-3">{{ $ep->has_sub ? 'Yes' : 'No' }}</td>
                    <td class="p-3">{{ $ep->has_dub ? 'Yes' : 'No' }}</td>
                    <td class="p-3 flex space-x-2">
                        <a href="{{ route('admin.anime.episodes.edit', [$anime, $ep]) }}" class="text-blue-500 hover:text-blue-400">Edit</a>
                        @if($ep->video_path && $ep->storage_disk === 'local')
                            <form action="{{ route('admin.anime.episodes.delete-video', [$anime, $ep]) }}" method="POST" onsubmit="return confirm('Delete video file?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-orange-500 hover:text-orange-400">Video</button>
                            </form>
                        @endif
                        <form action="{{ route('admin.anime.episodes.destroy', [$anime, $ep]) }}" method="POST" onsubmit="return confirm('Delete this episode?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $episodes->links() }}</div>
</div>
@endsection
