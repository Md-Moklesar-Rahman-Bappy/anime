@if ($paginator->hasPages())
<nav class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">

    <!-- Info -->
    <div class="text-sm text-gray-500">
        Showing
        <span class="text-gray-300 font-medium">{{ $paginator->firstItem() }}</span>
        to
        <span class="text-gray-300 font-medium">{{ $paginator->lastItem() }}</span>
        of
        <span class="text-gray-300 font-medium">{{ $paginator->total() }}</span>
    </div>

    <!-- Pagination -->
    <ul class="flex items-center gap-1 text-sm">

        <!-- Previous -->
        @if ($paginator->onFirstPage())
            <li>
                <span class="pagination-disabled">‹</span>
            </li>
        @else
            <li>
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn">
                    ‹
                </a>
            </li>
        @endif

        <!-- Pages -->
        @foreach ($elements as $element)

            <!-- Dots -->
            @if (is_string($element))
                <li>
                    <span class="px-2 text-gray-600">
                        {{ $element }}
                    </span>
                </li>
            @endif

            <!-- Numbers -->
            @if (is_array($element))
                @foreach ($element as $page => $url)

                    @if ($page == $paginator->currentPage())
                        <li>
                            <span class="pagination-active">
                                {{ $page }}
                            </span>
                        </li>
                    @else
                        <li>
                            <a href="{{ $url }}" class="pagination-btn">
                                {{ $page }}
                            </a>
                        </li>
                    @endif

                @endforeach
            @endif

        @endforeach

        <!-- Next -->
        @if ($paginator->hasMorePages())
            <li>
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn">
                    ›
                </a>
            </li>
        @else
            <li>
                <span class="pagination-disabled">›</span>
            </li>
        @endif

    </ul>

</nav>
@endif

<style>
.pagination-btn {
    @apply px-3 py-1.5 bg-[#111827] border border-gray-800 rounded-lg text-gray-300 hover:text-white hover:bg-[#1f2937] transition;
}

.pagination-active {
    @apply px-3 py-1.5 bg-indigo-600 text-white rounded-lg border border-indigo-500;
}

.pagination-disabled {
    @apply px-3 py-1.5 bg-[#111827] border border-gray-800 text-gray-600 rounded-lg cursor-not-allowed;
}
</style>