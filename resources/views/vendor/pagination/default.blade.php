@if ($paginator->hasPages())
<nav class="flex justify-center mt-8">

    <ul class="flex items-center gap-1 text-sm">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li>
                <span class="pagination-disabled">‹</span>
            </li>
        @else
            <li>
                <a href="{{ $paginator->previousPageUrl() }}"
                   rel="prev"
                   class="pagination-btn">
                    ‹
                </a>
            </li>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)

            {{-- Dots --}}
            @if (is_string($element))
                <li>
                    <span class="px-2 text-gray-500">
                        {{ $element }}
                    </span>
                </li>
            @endif

            {{-- Page numbers --}}
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

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li>
                <a href="{{ $paginator->nextPageUrl() }}"
                   rel="next"
                   class="pagination-btn">
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