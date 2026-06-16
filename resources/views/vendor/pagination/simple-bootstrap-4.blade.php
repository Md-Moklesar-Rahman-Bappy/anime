@if ($paginator->hasPages())
<nav class="flex justify-center mt-6">

    <div class="flex items-center gap-2 text-sm">

        <!-- Previous -->
        @if ($paginator->onFirstPage())
            <span class="pagination-disabled">
                ← Previous
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="pagination-btn">
                ← Previous
            </a>
        @endif

        <!-- Next -->
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
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

<style>
.pagination-btn {
    @apply px-4 py-2 bg-[#111827] border border-gray-800 rounded-lg text-gray-300 hover:text-white hover:bg-[#1f2937] transition;
}

.pagination-disabled {
    @apply px-4 py-2 bg-[#111827] border border-gray-800 text-gray-600 rounded-lg cursor-not-allowed;
}
</style>