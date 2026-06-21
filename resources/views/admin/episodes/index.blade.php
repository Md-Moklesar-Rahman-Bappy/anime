@extends('admin.layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">

        <div>
            <h1 class="text-xl font-semibold text-white">
                Episodes: {{ $anime->title }}
            </h1>

            <a href="{{ route('admin.anime.index') }}"
               class="text-sm text-gray-500 hover:text-white">
                ← Back to Anime
            </a>
        </div>

        <div class="flex items-center gap-3 flex-wrap">

            {{-- REFRESH --}}
            @if($anime->mal_id)
                <form action="{{ route('admin.jikan.refresh-episodes', $anime->mal_id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-success text-sm flex items-center gap-1">

                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 4v5h.5m15 2A8 8 0 004.5 9M4 9h4m12 11v-5h-.5m0 0A8 8 0 014 15"/>
                        </svg>

                        Refresh
                    </button>
                </form>
            @endif

            {{-- ADD --}}
            <a href="{{ route('admin.anime.episodes.create', $anime) }}"
               class="btn-primary text-sm">
                Add Episode
            </a>

            {{-- QUICK IMPORT --}}
            <div x-data="{ open: false }" class="relative">

                <button @click="open = !open"
                        class="btn-cancel text-sm">
                    Quick Import
                </button>

                <div x-show="open"
                     @click.outside="open = false"
                     x-transition
                     class="absolute right-0 mt-2 w-40 bg-gray-900 border border-gray-700 rounded-lg shadow-lg z-50">

                    <a href="{{ route('admin.anime.episodes.create', $anime) }}?source=youtube"
                       class="block px-3 py-2 text-sm text-gray-300 hover:bg-gray-800">
                        From YouTube
                    </a>

                </div>

            </div>

        </div>

    </div>


    {{-- TABLE --}}
    <div class="table-card">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                {{-- HEADER --}}
                <thead class="table-head">
                    <tr>
                        <th class="p-4 text-left">#</th>
                        <th class="p-4 text-left">Title</th>
                        <th class="p-4 text-left">Source</th>
                        <th class="p-4 text-left">Duration</th>
                        <th class="p-4 text-left">Sub</th>
                        <th class="p-4 text-left">Dub</th>
                        <th class="p-4 text-left">Actions</th>
                    </tr>
                </thead>

                {{-- BODY --}}
                <tbody>

                @forelse($episodes as $ep)

                <tr class="table-row">

                    {{-- NUMBER --}}
                    <td class="p-4 text-white">
                        {{ $ep->number }}
                    </td>

                    {{-- TITLE --}}
                    <td class="p-4 text-gray-300">
                        {{ $ep->title ?? 'Episode '.$ep->number }}
                    </td>

                    {{-- SOURCE --}}
                    <td class="p-4">
                        @php
                            $badge = match($ep->source_type) {
                                'youtube' => 'badge-danger',
                                'upload' => 'badge-success',
                                'external' => 'badge-indigo',
                                default => 'bg-gray-700 text-gray-300 px-2 py-1 text-xs rounded',
                            };
                        @endphp

                        <span class="{{ $badge }}">
                            {{ ucfirst($ep->source_type ?? '-') }}
                        </span>
                    </td>

                    {{-- DURATION --}}
                    <td class="p-4 text-gray-400">
                        {{ $ep->duration ? $ep->duration . 'm' : '-' }}
                    </td>

                    {{-- SUB --}}
                    <td class="p-4 text-gray-300">
                        {{ $ep->has_sub ? '✔' : '—' }}
                    </td>

                    {{-- DUB --}}
                    <td class="p-4 text-gray-300">
                        {{ $ep->has_dub ? '✔' : '—' }}
                    </td>

                    {{-- ACTIONS --}}
                    <td class="p-4">

                        <div class="flex flex-wrap items-center gap-3 text-sm">

                            <a href="{{ route('admin.anime.episodes.edit', [$anime, $ep]) }}"
                               class="text-blue-400 hover:text-blue-300">
                                Edit
                            </a>

                            @if($ep->video_path && $ep->storage_disk === 'local')
                                <form action="{{ route('admin.anime.episodes.delete-video', [$anime, $ep]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete video file?')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="text-orange-400 hover:text-orange-300">
                                        Video
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('admin.anime.episodes.destroy', [$anime, $ep]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this episode?')">
                                @csrf
                                @method('DELETE')

                                <button class="text-red-400 hover:text-red-300">
                                    Delete
                                </button>
                            </form>

                        </div>

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="7" class="p-10 text-center text-gray-500">

                        <p class="text-white font-medium mb-1">
                            No episodes found
                        </p>

                        <p class="text-sm">
                            Add your first episode
                        </p>

                    </td>
                </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        <div class="p-4 border-t border-gray-700">
            {{ $episodes->links() }}
        </div>

    </div>

</div>

@endsection