<div class="table-card">

    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            {{-- HEADER --}}
            <thead class="table-head">
                <tr>
                    <th class="p-4 text-left">Title</th>
                    <th class="p-4 text-left">Type</th>
                    <th class="p-4 text-left">Status</th>
                    <th class="p-4 text-left">Episodes</th>
                    <th class="p-4 text-left">Actions</th>
                </tr>
            </thead>

            {{-- BODY --}}
            <tbody>

            @forelse($animeList as $anime)

            <tr class="table-row">

                {{-- TITLE --}}
                <td class="p-4">
                    <div class="flex items-center gap-3">

                        @if($anime->thumbnail)
                        <div class="w-10 h-14 rounded overflow-hidden bg-gray-800 flex-shrink-0">
                            <img src="{{ $anime->thumbnail_url }}"
                                 class="w-full h-full object-cover">
                        </div>
                        @endif

                        <span class="text-white font-medium truncate max-w-[180px]">
                            {{ $anime->title }}
                        </span>

                    </div>
                </td>

                {{-- TYPE --}}
                <td class="p-4 text-gray-400">
                    {{ $anime->type ?? '—' }}
                </td>

                {{-- STATUS --}}
                <td class="p-4">
                    @php
                        $statusClass = match($anime->status) {
                            'Completed' => 'badge-success',
                            'Ongoing' => 'badge-indigo',
                            'Upcoming' => 'badge-warning',
                            default => 'bg-gray-700 text-gray-300 px-2 py-1 text-xs rounded',
                        };
                    @endphp

                    <span class="{{ $statusClass }}">
                        {{ $anime->status ?? 'N/A' }}
                    </span>
                </td>

                {{-- EPISODES --}}
                <td class="p-4 text-gray-400">
                    {{ $anime->episodes_count }}
                </td>

                {{-- ACTIONS --}}
                <td class="p-4">

                    <div class="flex flex-wrap items-center gap-3 text-sm">

                        <a href="{{ route('admin.anime.episodes.index', $anime) }}"
                           class="text-indigo-400 hover:text-indigo-300">
                            Episodes
                        </a>

                        <a href="{{ route('admin.anime.edit', $anime) }}"
                           class="text-blue-400 hover:text-blue-300">
                            Edit
                        </a>

                        @if($anime->mal_id)
                        <form action="{{ route('admin.jikan.refresh-anime', $anime->mal_id) }}"
                              method="POST"
                              onsubmit="return confirm('Refresh {{ $anime->title }} from MAL?')">
                            @csrf
                            <button type="submit"
                                    class="text-green-400 hover:text-green-300">
                                Refresh
                            </button>
                        </form>
                        @endif

                        <form action="{{ route('admin.anime.destroy', $anime) }}"
                              method="POST"
                              onsubmit="return confirm('Delete this anime?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="text-red-400 hover:text-red-300">
                                Delete
                            </button>
                        </form>

                    </div>

                </td>

            </tr>

            @empty

            <tr>
                <td colspan="5" class="p-10 text-center text-gray-500">

                    <p class="text-white font-medium mb-1">
                        No anime found
                    </p>

                    <p class="text-sm">
                        Try searching or add new anime
                    </p>

                </td>
            </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    {{-- PAGINATION --}}
    <div class="p-4 border-t border-gray-700">
        @include('admin.anime._pagination')
    </div>

</div>