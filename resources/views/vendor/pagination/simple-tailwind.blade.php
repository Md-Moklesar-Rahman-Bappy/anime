@if ($paginator->hasPages())
<nav class="flex justify-center mt-8">

    <div class="flex items-center gap-3 text-sm">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-disabled">
                ← Previous
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               rel="prev"
               class="pagination-btn">
                ← Previous
            </a>
        @endif

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               rel="next"
               class="pagination-btn">
                Next →
            </a>
        @else
            <span class="pagination-disabled">
                Next →
            </span>
        @endif

    </div>

</nav>
@endif