@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">

        <div>
            <h1 class="text-2xl font-semibold text-white">
                Episodes: {{ $anime->title }}
            </h1>
            <a href="{{ route('admin.anime.index') }}"
               class="text-sm text-gray-400 hover:text-white transition">
                ← Back to Anime
            </a>
        </div>

        <div class="flex gap-2">

            @if($anime->mal_id)
            <form action="{{ route('admin.jikan.refresh-episodes', $anime->mal_id) }}" method="POST">
                @csrf
                <button type="submit"
                    class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1 transition">
                    
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                              d="M4 4v5h.5m15 2A8 8 0 004.5 9M4 9h4m12 11v-5h-.5m0 0A8 8 0 014 15"/>
                    </svg>

                    Refresh
                </button>
            </form>
            @endif

            <a href="{{ route('admin.anime.episodes.create', $anime) }}"
               class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm transition">
                Add Episode
            </a>

            <!-- Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="bg-[#1f2937] hover:bg-gray-700 px-4 py-2 rounded-lg text-sm text-gray-300 border border-gray-700">
                    Quick Import
                </button>

                <div x-show="open" @click.outside="open=false"
                     class="absolute right-0 mt-2 w-48 bg-[#111827] border border-gray-800 rounded-lg shadow-lg z-50">

                    <a href="{{ route('admin.anime.episodes.create', $anime) }}?source=youtube"
                       class="block px-4 py-2 text-sm text-gray-300 hover:bg-[#1f2937]">
                        From YouTube
                    </a>

                </div>
            </div>

        </div>
    </div>

    <!-- Table -->
    <div class="bg-[#111827] border border-gray-800 rounded-2xl overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead>
                <tr class="bg-[#0f172a] text-gray-400 border-b border-gray-800">
                    <th class="p-3 text-left">#</th>
                    <th class="p-3 text-left">Title</th>
                    <th class="p-3 text-left">Source</th>
                    <th class="p-3 text-left">Duration</th>
                    <th class="p-3 text-left">Sub</th>
                    <th class="p-3 text-left">Dub</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
                </thead>

                <tbody>
                @forelse($episodes as $ep)
                <tr class="border-b border-gray-800 hover:bg-[#1f2937] transition">

                    <td class="p-3 text-white">{{ $ep->number }}</td>

                    <td class="p-3 text-gray-300">
                        {{ $ep->title ?? 'Episode '.$ep->number }}
                    </td>

                    <!-- Source -->
                    <td class="p-3">
                        <span class="text-xs px-2 py-1 rounded-lg
                            @switch($ep->source_type)
                                @case('youtube') bg-red-500/10 text-red-400 @break
                                @case('upload') bg-green-500/10 text-green-400 @break
                                @case('external') bg-cyan-500/10 text-cyan-400 @break
                                @default bg-gray-700 text-gray-400
                            @endswitch
                        ">
                            {{ ucfirst($ep->source_type ?? '-') }}
                        </span>
                    </td>

                    <td class="p-3 text-gray-400">
                        {{ $ep->duration ? $ep->duration.'m' : '-' }}
                    </td>

                    <td class="p-3 text-gray-300">
                        {{ $ep->has_sub ? '✔' : '—' }}
                    </td>

                    <td class="p-3 text-gray-300">
                        {{ $ep->has_dub ? '✔' : '—' }}
                    </td>

                    <!-- Actions -->
                    <td class="p-3 flex gap-3 text-sm">

                        <a href="{{ route('admin.anime.episodes.edit', [$anime, $ep]) }}"
                           class="text-blue-400 hover:text-blue-300 transition">
                            Edit
                        </a>

                        @if($ep->video_path && $ep->storage_disk === 'local')
                        <form action="{{ route('admin.anime.episodes.delete-video', [$anime, $ep]) }}"
                              method="POST"
                              onsubmit="return confirm('Delete video file?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-orange-400 hover:text-orange-300 transition">
                                Video
                            </button>
                        </form>
                        @endif

                        <form action="{{ route('admin.anime.episodes.destroy', [$anime, $ep]) }}"
                              method="POST"
                              onsubmit="return confirm('Delete this episode?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-400 hover:text-red-300 transition">
                                Delete
                            </button>
                        </form>

                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-10 text-center text-gray-500">
                        <p class="text-lg text-gray-300">No episodes found</p>
                        <p class="text-sm mt-1">Add your first episode</p>
                    </td>
                </tr>
                @endforelse
                </tbody>

            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-800">
            {{ $episodes->links() }}
        </div>

    </div>
</div>
@endsection