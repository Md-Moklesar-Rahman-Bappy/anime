@if ($paginator->hasPages())
    <nav role="navigation"
         aria-label="Pagination Navigation"
         class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">

        {{-- ─────── RESULTS INFO ─────── --}}
        <p class="text-xs text-gray-500 order-2 sm:order-1">
            Showing
            <span class="font-semibold text-gray-300">{{ $paginator->firstItem() ?? 0 }}</span>
            to
            <span class="font-semibold text-gray-300">{{ $paginator->lastItem() ?? 0 }}</span>
            of
            <span class="font-semibold text-gray-300">{{ $paginator->total() }}</span>
            results
        </p>

        {{-- ─────── PAGE BUTTONS ─────── --}}
        <ul class="flex items-center gap-1 text-sm order-1 sm:order-2">

            {{-- FIRST PAGE --}}
            @if ($paginator->currentPage() > 2)
                <li class="hidden sm:block">
                    {{ $paginator->url(1) }}
                       class="pagination-btn"
                       aria-label="Go to first page"
                       title="First page">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                    </a>
                </li>
            @endif

            {{-- PREVIOUS --}}
            @if ($paginator->onFirstPage())
                <li>
                    <span class="pagination-disabled" aria-disabled="true" aria-label="Previous page (disabled)">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </span>
                </li>
            @else
                <li>
                    {{ $paginator->previousPageUrl() }}
                       rel="prev"
                       class="pagination-btn"
                       aria-label="Go to previous page">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                </li>
            @endif

            {{-- PAGE NUMBERS --}}
            @foreach ($elements as $element)

                {{-- Dots separator --}}
                @if (is_string($element))
                    <li class="hidden sm:block">
                        <span class="px-2 text-gray-600 select-none">{{ $element }}</span>
                    </li>
                @endif

                {{-- Page numbers --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)

                        @if ($page == $paginator->currentPage())
                            <li>
                                <span class="pagination-active"
                                      aria-current="page"
                                      aria-label="Current page, page {{ $page }}">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            @php
                                $distance = abs($page - $paginator->currentPage());
                                $hideClass = $distance > 1 ? 'hidden sm:block' : '';
                            @endphp

                            <li class="{{ $hideClass }}">
                                {{ $url }}
                                   class="pagination-btn"
                                   aria-label="Go to page {{ $page }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif

                    @endforeach
                @endif

            @endforeach

            {{-- NEXT --}}
            @if ($paginator->hasMorePages())
                <li>
                    {{ $paginator->nextPageUrl() }}
                       rel="next"
                       class="pagination-btn"
                       aria-label="Go to next page">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </li>
            @else
                <li>
                    <span class="pagination-disabled" aria-disabled="true" aria-label="Next page (disabled)">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </li>
            @endif

            {{-- LAST PAGE --}}
            @if ($paginator->currentPage() < $paginator->lastPage() - 1)
                <li class="hidden sm:block">
                    {{ $paginator->url($paginator->lastPage()) }}
                       class="pagination-btn"
                       aria-label="Go to last page"
                       title="Last page">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                        </svg>
                    </a>
                </li>
            @endif

        </ul>

    </nav>
@endif