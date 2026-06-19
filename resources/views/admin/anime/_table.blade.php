@forelse($animeList as $anime)
<tr style="border-bottom:1px solid #374151">

    <td class="p-3">
        <div class="d-flex align-items-center gap-3">
            @if($anime->thumbnail)
            <div style="width:40px;height:56px;border-radius:0.375rem;overflow:hidden;background:#1f2937;flex-shrink:0">
                <img src="{{ $anime->thumbnail_url }}" style="width:100%;height:100%;object-fit:cover">
            </div>
            @endif
            <span class="text-white fw-medium text-truncate" style="max-width:180px">
                {{ $anime->title }}
            </span>
        </div>
    </td>

    <td class="p-3" style="color:#9ca3af">
        {{ $anime->type ?? '—' }}
    </td>

    <td class="p-3">
        <span class="badge rounded-1 fw-normal"
            style="font-size:0.75rem;
                @if($anime->status === 'Completed') background:rgba(34,197,94,0.1);color:#4ade80
                @elseif($anime->status === 'Ongoing') background:rgba(99,102,241,0.1);color:#818cf8
                @elseif($anime->status === 'Upcoming') background:rgba(234,179,8,0.1);color:#facc15
                @else background:#374151;color:#9ca3af
                @endif
            ">
            {{ $anime->status ?? 'N/A' }}
        </span>
    </td>

    <td class="p-3" style="color:#9ca3af">
        {{ $anime->episodes_count }}
    </td>

    <td class="p-3">
        <div class="d-flex flex-wrap align-items-center gap-3 small">

            <a href="{{ route('admin.anime.episodes.index', $anime) }}"
               style="color:#818cf8">
                Episodes
            </a>

            <a href="{{ route('admin.anime.edit', $anime) }}"
               style="color:#60a5fa">
                Edit
            </a>

            @if($anime->mal_id)
            <form action="{{ route('admin.jikan.refresh-anime', $anime->mal_id) }}"
                  method="POST"
                  onsubmit="return confirm('Refresh {{ $anime->title }} from MAL?')">
                @csrf
                <button class="btn btn-sm border-0 p-0" style="color:#4ade80">
                    Refresh
                </button>
            </form>
            @endif

            <form action="{{ route('admin.anime.destroy', $anime) }}"
                  method="POST"
                  onsubmit="return confirm('Delete this anime?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm border-0 p-0" style="color:#f87171">
                    Delete
                </button>
            </form>

        </div>
    </td>

</tr>
@empty
<tr>
    <td colspan="5" class="p-5 text-center" style="color:#6b7280">
        <p class="h5" style="color:#d1d5db">No anime found</p>
        <p class="small mt-1">Try searching or add new anime</p>
    </td>
</tr>
@endforelse