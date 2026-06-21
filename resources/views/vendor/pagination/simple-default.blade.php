@if ($paginator->hasPages())
    <nav role="navigation"
         aria-label="Pagination Navigation"
         class="mt-8 flex items-center justify-between gap-3">

        {{-- ─────── PREVIOUS ─────── --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-disabled" aria-disabled="true" aria-label="Previous page (disabled)">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="ml-1.5 hidden sm:inline">Previous</span>
            </span>
        @else
            {{ $paginator->previousPageUrl() }}
               rel="prev"
               class="pagination-btn"
               aria-label="Go to previous page">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="ml-1.5 hidden sm:inline">Previous</span>
            </a>
        @endif

        {{-- ─────── CURRENT PAGE INDICATOR ─────── --}}
        <span class="text-xs text-gray-500">
            Page
            <strong class="text-gray-300 font-semibold">{{ $paginator->currentPage() }}</strong>
        </span>

        {{-- ─────── NEXT ─────── --}}
        @if ($paginator->hasMorePages())
            {{ $paginator->nextPageUrl() }}
               rel="next"
               class="pagination-btn"
               aria-label="Go to next page">
                <span class="mr-1.5 hidden sm:inline">Next</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @else
            <span class="pagination-disabled" aria-disabled="true" aria-label="Next page (disabled)">
                <span class="mr-1.5 hidden sm:inline">Next</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </span>
        @endif

    </nav>
@endif