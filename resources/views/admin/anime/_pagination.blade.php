@if ($animeList->hasPages())
<div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">

    {{-- INFO --}}
    <p class="text-sm text-gray-400">
        Showing
        <span class="text-white">{{ $animeList->firstItem() }}</span> –
        <span class="text-white">{{ $animeList->lastItem() }}</span>
        of
        <span class="text-white">{{ $animeList->total() }}</span>
    </p>

    {{-- PAGINATION --}}
    <div class="flex items-center gap-1 text-sm">

        {{-- PREV --}}
        @if ($animeList->onFirstPage())
            <span class="pagination-disabled px-3 py-2">Prev</span>
        @else
            <button
                data-page="{{ $animeList->currentPage() - 1 }}"
                class="pagination-btn px-3 py-2 paginate-link">
                Prev
            </button>
        @endif

        @php
            $current = $animeList->currentPage();
            $last = $animeList->lastPage();
            $start = max(1, $current - 2);
            $end = min($last, $current + 2);
        @endphp

        {{-- LEFT DOTS --}}
        @if ($start > 1)
            <span class="px-2 text-gray-500">...</span>
        @endif

        {{-- PAGE NUMBERS --}}
        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $current)
                <span class="pagination-active px-3 py-2">
                    {{ $page }}
                </span>
            @else
                <button
                    data-page="{{ $page }}"
                    class="pagination-btn px-3 py-2 paginate-link">
                    {{ $page }}
                </button>
            @endif
        @endfor

        {{-- RIGHT DOTS --}}
        @if ($end < $last)
            <span class="px-2 text-gray-500">...</span>
        @endif

        {{-- NEXT --}}
        @if ($animeList->hasMorePages())
            <button
                data-page="{{ $animeList->currentPage() + 1 }}"
                class="pagination-btn px-3 py-2 paginate-link">
                Next
            </button>
        @else
            <span class="pagination-disabled px-3 py-2">Next</span>
        @endif

    </div>

</div>
@endif