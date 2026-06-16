@if ($paginator->hasPages())
<nav class="flex justify-center mt-6">

    <ul class="flex items-center gap-1 text-sm">

        <!-- Previous -->
        @if ($paginator->onFirstPage())
            <li>
                <span class="px-3 py-2 text-gray-600 bg-[#111827] border border-gray-800 rounded-lg cursor-not-allowed">
                    ‹
                </span>
            </li>
        @else
            <li>
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="pagination-btn">
                    ‹
                </a>
            </li>
        @endif

        <!-- Pages -->
        @foreach ($elements as $element)

            <!-- ... -->
            @if (is_string($element))
                <li>
                    <span class="px-3 py-2 text-gray-500">
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
                            <a href="{{ $url }}"
                               class="pagination-btn">
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
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="pagination-btn">
                    ›
                </a>
            </li>
        @else
            <li>
                <span class="px-3 py-2 text-gray-600 bg-[#111827] border border-gray-800 rounded-lg cursor-not-allowed">
                    ›
                </span>
            </li>
        @endif

    </ul>

</nav>
@endif

<style>
.pagination-btn {
    @apply px-3 py-2 bg-[#111827] border border-gray-800 rounded-lg text-gray-300 hover:text-white hover:bg-[#1f2937] transition;
}

.pagination-active {
    @apply px-3 py-2 bg-indigo-600 text-white rounded-lg border border-indigo-500;
}
</style>