@if ($animeList->hasPages())
<div class="flex items-center justify-between">
    <p class="text-sm text-gray-400">
        Showing {{ $animeList->firstItem() }}–{{ $animeList->lastItem() }} of {{ $animeList->total() }}
    </p>
    <div class="flex items-center gap-1">
        @if ($animeList->onFirstPage())
            <span class="px-3 py-1.5 rounded text-sm text-gray-600 bg-gray-800 cursor-not-allowed">Previous</span>
        @else
            <button data-page="{{ $animeList->currentPage() - 1 }}" class="px-3 py-1.5 rounded text-sm text-gray-300 bg-gray-800 hover:bg-gray-700 paginate-link">Previous</button>
        @endif

        @php
            $current = $animeList->currentPage();
            $last = $animeList->lastPage();
            $start = max(1, $current - 2);
            $end = min($last, $current + 2);
        @endphp

        @if ($start > 1)
            <span class="px-3 py-1.5 text-sm text-gray-600">...</span>
        @endif

        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $current)
                <span class="px-3 py-1.5 rounded text-sm bg-purple-600 text-white">{{ $page }}</span>
            @else
                <button data-page="{{ $page }}" class="px-3 py-1.5 rounded text-sm text-gray-300 bg-gray-800 hover:bg-gray-700 paginate-link">{{ $page }}</button>
            @endif
        @endfor

        @if ($end < $last)
            <span class="px-3 py-1.5 text-sm text-gray-600">...</span>
        @endif

        @if ($animeList->hasMorePages())
            <button data-page="{{ $animeList->currentPage() + 1 }}" class="px-3 py-1.5 rounded text-sm text-gray-300 bg-gray-800 hover:bg-gray-700 paginate-link">Next</button>
        @else
            <span class="px-3 py-1.5 rounded text-sm text-gray-600 bg-gray-800 cursor-not-allowed">Next</span>
        @endif
    </div>
</div>
@endif
