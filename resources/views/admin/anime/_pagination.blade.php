@if ($animeList->hasPages())
<div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">

    <!-- Info -->
    <p class="text-sm text-gray-400">
        Showing {{ $animeList->firstItem() }}–{{ $animeList->lastItem() }} of {{ $animeList->total() }}
    </p>

    <!-- Pagination -->
    <div class="flex items-center gap-2">

        {{-- Previous --}}
        @if ($animeList->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-sm text-gray-600 bg-[#111827] cursor-not-allowed">
                Prev
            </span>
        @else
            <button data-page="{{ $animeList->currentPage() - 1 }}"
                class="px-3 py-1.5 rounded-lg text-sm text-gray-300 bg-[#1f2937] hover:bg-indigo-600 transition paginate-link">
                Prev
            </button>
        @endif

        @php
            $current = $animeList->currentPage();
            $last = $animeList->lastPage();
            $start = max(1, $current - 2);
            $end = min($last, $current + 2);
        @endphp

        {{-- Start dots --}}
        @if ($start > 1)
            <span class="px-2 text-gray-500">...</span>
        @endif

        {{-- Pages --}}
        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $current)
                <span class="px-3 py-1.5 rounded-lg text-sm bg-indigo-600 text-white font-semibold">
                    {{ $page }}
                </span>
            @else
                <button data-page="{{ $page }}"
                    class="px-3 py-1.5 rounded-lg text-sm text-gray-300 bg-[#1f2937] hover:bg-indigo-600 transition paginate-link">
                    {{ $page }}
                </button>
            @endif
        @endfor

        {{-- End dots --}}
        @if ($end < $last)
            <span class="px-2 text-gray-500">...</span>
        @endif

        {{-- Next --}}
        @if ($animeList->hasMorePages())
            <button data-page="{{ $animeList->currentPage() + 1 }}"
                class="px-3 py-1.5 rounded-lg text-sm text-gray-300 bg-[#1f2937] hover:bg-indigo-600 transition paginate-link">
                Next
            </button>
        @else
            <span class="px-3 py-1.5 rounded-lg text-sm text-gray-600 bg-[#111827] cursor-not-allowed">
                Next
            </span>
        @endif

    </div>
</div>
@endif