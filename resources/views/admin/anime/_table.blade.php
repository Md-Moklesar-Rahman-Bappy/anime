@forelse($animeList as $anime)
<tr class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
    <td class="p-3">
        <div class="flex items-center gap-3">
            @if($anime->thumbnail)
            <div class="w-10 h-14 rounded overflow-hidden bg-gray-800 flex-shrink-0">
                <img src="{{ $anime->thumbnail_url }}" alt="" class="w-full h-full object-cover">
            </div>
            @endif
            <span class="font-medium truncate max-w-48">{{ $anime->title }}</span>
        </div>
    </td>
    <td class="p-3 text-gray-400">{{ $anime->type ?? 'N/A' }}</td>
    <td class="p-3">
        <span class="px-2 py-0.5 rounded text-xs font-medium
            @if($anime->status === 'Completed') bg-green-900 text-green-300
            @elseif($anime->status === 'Ongoing') bg-blue-900 text-blue-300
            @elseif($anime->status === 'Upcoming') bg-yellow-900 text-yellow-300
            @else bg-gray-800 text-gray-400 @endif">
            {{ $anime->status ?? 'N/A' }}
        </span>
    </td>
    <td class="p-3 text-gray-400">{{ $anime->episodes_count }}</td>
    <td class="p-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.anime.episodes.index', $anime) }}" class="text-purple-500 hover:text-purple-400 text-sm">Episodes</a>
            <a href="{{ route('admin.anime.edit', $anime) }}" class="text-blue-500 hover:text-blue-400 text-sm">Edit</a>
            <form action="{{ route('admin.anime.destroy', $anime) }}" method="POST" onsubmit="return confirm('Delete this anime?')" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-500 hover:text-red-400 text-sm">Delete</button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="p-8 text-center text-gray-500">
        <p class="text-lg mb-1">No anime found</p>
        <p class="text-sm">Try adjusting your search or add a new anime.</p>
    </td>
</tr>
@endforelse
