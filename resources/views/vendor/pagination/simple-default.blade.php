@if ($paginator->hasPages())
<nav class="flex justify-center mt-8">

    <ul class="flex items-center gap-1 text-sm">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-disabled">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn">‹</a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)

            @if (is_string($element))
                <span class="px-2 text-gray-500">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)

                    @if ($page == $paginator->currentPage())
                        <span class="pagination-active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                    @endif

                @endforeach
            @endif

        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn">›</a>
        @else
            <span class="pagination-disabled">›</span>
        @endif

    </ul>

</nav>
@endif