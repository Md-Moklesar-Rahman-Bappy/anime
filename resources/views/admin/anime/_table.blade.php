@forelse($animeList as $anime)
<tr class="border-b border-gray-800 hover:bg-[#1f2937] transition">

    <!-- Title -->
    <td class="p-3">
        <div class="flex items-center gap-3">

            @if($anime->thumbnail)
            <div class="w-10 h-14 rounded-md overflow-hidden bg-[#1f2937] flex-shrink-0">
                <img src="{{ $anime->thumbnail_url }}" class="w-full h-full object-cover">
            </div>
            @endif

            <span class="text-white font-medium truncate max-w-[180px]">
                {{ $anime->title }}
            </span>
        </div>
    </td>

    <!-- Type -->
    <td class="p-3 text-gray-400">
        {{ $anime->type ?? '—' }}
    </td>

    <!-- Status -->
    <td class="p-3">
        <span class="px-2 py-1 rounded-lg text-xs font-medium
            @if($anime->status === 'Completed') bg-green-500/10 text-green-400
            @elseif($anime->status === 'Ongoing') bg-indigo-500/10 text-indigo-400
            @elseif($anime->status === 'Upcoming') bg-yellow-500/10 text-yellow-400
            @else bg-gray-700 text-gray-400
            @endif
        ">
            {{ $anime->status ?? 'N/A' }}
        </span>
    </td>

    <!-- Episodes -->
    <td class="p-3 text-gray-400">
        {{ $anime->episodes_count }}
    </td>

    <!-- Actions -->
    <td class="p-3">
        <div class="flex flex-wrap items-center gap-3 text-sm">

            <a href="{{ route('admin.anime.episodes.index', $anime) }}"
               class="text-indigo-400 hover:text-indigo-300 transition">
                Episodes
            </a>

            <a href="{{ route('admin.anime.edit', $anime) }}"
               class="text-blue-400 hover:text-blue-300 transition">
                Edit
            </a>

            @if($anime->mal_id)
            <form action="{{ route('admin.jikan.refresh-anime', $anime->mal_id) }}"
                  method="POST"
                  onsubmit="return confirm('Refresh {{ $anime->title }} from MAL?')">
                @csrf
                <button class="text-green-400 hover:text-green-300 transition">
                    Refresh
                </button>
            </form>
            @endif

            <form action="{{ route('admin.anime.destroy', $anime) }}"
                  method="POST"
                  onsubmit="return confirm('Delete this anime?')">
                @csrf
                @method('DELETE')
                <button class="text-red-400 hover:text-red-300 transition">
                    Delete
                </button>
            </form>

        </div>
    </td>

</tr>
@empty
<tr>
    <td colspan="5" class="p-10 text-center text-gray-500">
        <p class="text-lg text-gray-300">No anime found</p>
        <p class="text-sm mt-1">Try searching or add new anime</p>
    </td>
</tr>
@endforelse